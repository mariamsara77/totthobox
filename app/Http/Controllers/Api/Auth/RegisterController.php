<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Throwable;

class RegisterController extends Controller
{
    private const OTP_TTL_MINUTES = 10;
    private const OTP_VERIFY_ATTEMPTS = 5;
    private const OTP_RESEND_SECONDS = 60;

    private function normalizedEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    private function otpKey(string $email): string
    {
        return 'register_otp:' . $this->normalizedEmail($email);
    }

    private function payloadKey(string $email): string
    {
        return 'register_payload:' . $this->normalizedEmail($email);
    }

    private function verifyThrottleKey(Request $request, string $email): string
    {
        return 'register_otp_verify:' . $request->ip() . ':' . $this->normalizedEmail($email);
    }

    private function sendThrottleKey(Request $request, string $email): string
    {
        return 'register_otp_send:' . $request->ip() . ':' . $this->normalizedEmail($email);
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

        $email = $this->normalizedEmail($validated['email']);
        $sendKey = $this->sendThrottleKey($request, $email);

        if (RateLimiter::tooManyAttempts($sendKey, 1)) {
            return response()->json([
                'message' => 'এক মিনিট পরে আবার কোড পাঠানোর চেষ্টা করুন।',
            ], 429, [
                'Retry-After' => (string) RateLimiter::availableIn($sendKey),
            ]);
        }

        $otp = (string) random_int(1000, 9999);

        Cache::put($this->otpKey($email), $otp, now()->addMinutes(self::OTP_TTL_MINUTES));
        Cache::put($this->payloadKey($email), [
            'name' => trim($validated['name']),
            'password' => Hash::make($validated['password']),
        ], now()->addMinutes(self::OTP_TTL_MINUTES));

        try {
            Mail::to($email)->send(new OtpMail($otp));
            RateLimiter::hit($sendKey, self::OTP_RESEND_SECONDS);
        } catch (Throwable $e) {
            Cache::forget($this->otpKey($email));
            Cache::forget($this->payloadKey($email));

            report($e);

            return response()->json([
                'message' => 'ভেরিফিকেশন কোড পাঠানো যায়নি। কিছুক্ষণ পরে আবার চেষ্টা করুন।',
            ], 503);
        }

        return response()->json([
            'success' => true,
            'message' => 'ভেরিফিকেশন কোড ইমেইলে পাঠানো হয়েছে।',
        ]);
    }

    public function resendOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ], [
            'email.required' => 'ইমেইল দিতে হবে।',
        ]);

        $email = $this->normalizedEmail($validated['email']);
        $payload = Cache::get($this->payloadKey($email));

        if (! $payload) {
            throw ValidationException::withMessages([
                'email' => ['সেশনের মেয়াদ শেষ হয়ে গেছে, আবার শুরু থেকে চেষ্টা করুন।'],
            ]);
        }

        $sendKey = $this->sendThrottleKey($request, $email);

        if (RateLimiter::tooManyAttempts($sendKey, 1)) {
            return response()->json([
                'message' => 'এক মিনিট পরে আবার কোড পাঠানোর চেষ্টা করুন।',
            ], 429, [
                'Retry-After' => (string) RateLimiter::availableIn($sendKey),
            ]);
        }

        $otp = (string) random_int(1000, 9999);
        Cache::put($this->otpKey($email), $otp, now()->addMinutes(self::OTP_TTL_MINUTES));

        try {
            Mail::to($email)->send(new OtpMail($otp));
            RateLimiter::hit($sendKey, self::OTP_RESEND_SECONDS);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'message' => 'নতুন কোড পাঠানো যায়নি। কিছুক্ষণ পরে আবার চেষ্টা করুন।',
            ], 503);
        }

        return response()->json([
            'success' => true,
            'message' => 'নতুন কোড পাঠানো হয়েছে।',
        ]);
    }

    public function verifyAndRegister(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:4'],
        ], [
            'email.required' => 'ইমেইল দিতে হবে।',
            'otp.required' => 'ভেরিফিকেশন কোড দিতে হবে।',
            'otp.digits' => 'ভেরিফিকেশন কোড ৪ সংখ্যার হতে হবে।',
        ]);

        $email = $this->normalizedEmail($validated['email']);
        $verifyKey = $this->verifyThrottleKey($request, $email);

        if (RateLimiter::tooManyAttempts($verifyKey, self::OTP_VERIFY_ATTEMPTS)) {
            return response()->json([
                'message' => 'অনেকবার ভুল কোড দেওয়া হয়েছে। কিছুক্ষণ পরে আবার চেষ্টা করুন।',
            ], 429, [
                'Retry-After' => (string) RateLimiter::availableIn($verifyKey),
            ]);
        }

        $cachedOtp = Cache::get($this->otpKey($email));
        $payload = Cache::get($this->payloadKey($email));

        if (! $cachedOtp || ! $payload) {
            throw ValidationException::withMessages([
                'otp' => ['কোডের মেয়াদ শেষ হয়ে গেছে, আবার চেষ্টা করুন।'],
            ]);
        }

        if (! hash_equals((string) $cachedOtp, (string) $validated['otp'])) {
            RateLimiter::hit($verifyKey, self::OTP_TTL_MINUTES * 60);

            throw ValidationException::withMessages([
                'otp' => ['কোডটি সঠিক নয়।'],
            ]);
        }

        RateLimiter::clear($verifyKey);

        $user = User::create([
            'name' => $payload['name'],
            'email' => $email,
            'password' => $payload['password'],
            'email_verified_at' => now(),
            'status' => 'active',
        ]);

        if (method_exists($user, 'assignRole')) {
            $user->assignRole('user');
        }

        Cache::forget($this->otpKey($email));
        Cache::forget($this->payloadKey($email));

        return response()->json([
            'success' => true,
            'message' => 'অ্যাকাউন্ট তৈরি হয়েছে।',
            'token' => $user->createToken('auth_token')->plainTextToken,
            'user' => $user->only(['id', 'name', 'email', 'slug', 'avatar_url']),
        ]);
    }
}
