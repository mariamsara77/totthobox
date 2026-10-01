<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    private function otpKey(string $email): string
    {
        return 'register_otp:'.strtolower($email);
    }

    private function payloadKey(string $email): string
    {
        return 'register_payload:'.strtolower($email);
    }

    public function sendOtp(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'নাম দিতে হবে।',
            'name.min' => 'নাম কমপক্ষে ৩ অক্ষরের হতে হবে।',
            'name.max' => 'নাম ৫০ অক্ষরের বেশি হতে পারবে না।',
            'email.required' => 'ইমেইল দিতে হবে।',
            'email.email' => 'সঠিক ইমেইল ফরম্যাট দিন।',
            'email.unique' => 'এই ইমেইল দিয়ে ইতিমধ্যে অ্যাকাউন্ট আছে।',
            'password.required' => 'পাসওয়ার্ড দিতে হবে।',
            'password.min' => 'পাসওয়ার্ড কমপক্ষে ৮ অক্ষরের হতে হবে।',
            'password.confirmed' => 'পাসওয়ার্ড মিলছে না।',
        ]);

        $otp = (string) random_int(1000, 9999);

        Cache::put($this->otpKey($validated['email']), $otp, now()->addMinutes(10));
        Cache::put($this->payloadKey($validated['email']), [
            'name' => $validated['name'],
            'password' => Hash::make($validated['password']),
        ], now()->addMinutes(10));

        // TODO: Mail::to($validated['email'])->send(new \App\Mail\RegisterOtpMail($otp));

        return response()->json([
            'success' => true,
            'message' => 'ভেরিফিকেশন কোড ইমেইলে পাঠানো হয়েছে।',
        ]);
    }

    public function resendOtp(Request $request)
    {
        $validated = $request->validate(['email' => ['required', 'email']], [
            'email.required' => 'ইমেইল দিতে হবে।',
        ]);

        $payload = Cache::get($this->payloadKey($validated['email']));

        if (! $payload) {
            throw ValidationException::withMessages([
                'email' => ['সেশনের মেয়াদ শেষ হয়ে গেছে, আবার শুরু থেকে চেষ্টা করুন।'],
            ]);
        }

        $otp = (string) random_int(1000, 9999);
        Cache::put($this->otpKey($validated['email']), $otp, now()->addMinutes(10));
        Cache::put($this->payloadKey($validated['email']), $payload, now()->addMinutes(10));

        // TODO: Mail::to($validated['email'])->send(new \App\Mail\RegisterOtpMail($otp));

        return response()->json([
            'success' => true,
            'message' => 'নতুন কোড পাঠানো হয়েছে।',
        ]);
    }

    public function verifyAndRegister(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'string'],
        ], [
            'email.required' => 'ইমেইল দিতে হবে।',
            'otp.required' => 'ভেরিফিকেশন কোড দিতে হবে।',
        ]);

        $cachedOtp = Cache::get($this->otpKey($validated['email']));
        $payload = Cache::get($this->payloadKey($validated['email']));

        if (! $cachedOtp || ! $payload) {
            throw ValidationException::withMessages([
                'otp' => ['কোডের মেয়াদ শেষ হয়ে গেছে, আবার চেষ্টা করুন।'],
            ]);
        }

        if ($cachedOtp !== $validated['otp']) {
            throw ValidationException::withMessages([
                'otp' => ['কোডটি সঠিক নয়।'],
            ]);
        }

        $user = User::create([
            'name' => $payload['name'],
            'email' => $validated['email'],
            'password' => $payload['password'],
            'email_verified_at' => now(),
        ]);

        Cache::forget($this->otpKey($validated['email']));
        Cache::forget($this->payloadKey($validated['email']));

        return response()->json([
            'success' => true,
            'message' => 'অ্যাকাউন্ট তৈরি হয়েছে।',
            'token' => $user->createToken('auth_token')->plainTextToken,
            'user' => $user->only(['id', 'name', 'email', 'slug', 'avatar_url']),
        ]);
    }
}