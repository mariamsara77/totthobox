<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Hash, DB, Mail, Cache, Cookie};
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    public function sendOtp(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:50'],
            'email' => ['required', 'string', 'lowercase', 'email:rfc,dns', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:255', Rules\Password::defaults()],
            'password_confirmation' => ['required', 'same:password'],
        ], [
            'name.required' => 'আপনার নাম দিতে হবে।',
            'name.min' => 'নাম অন্তত ৩ অক্ষরের হতে হবে।',
            'name.max' => 'নাম ৫০ অক্ষরের বেশি হতে পারবে না।',
            'email.required' => 'আপনার ইমেইল ঠিকানা দিতে হবে।',
            'email.email' => 'সঠিক ইমেইল ফরম্যাট ব্যবহার করুন।',
            'email.unique' => 'এই ইমেইলটি দিয়ে ইতিমধ্যে অ্যাকাউন্ট খোলা হয়েছে।',
            'password.required' => 'একটি পাসওয়ার্ড দিন।',
            'password.min' => 'পাসওয়ার্ডটি অন্তত ৮ অক্ষরের হতে হবে।',
            'password_confirmation.required' => 'পাসওয়ার্ডটি আবার লিখুন।',
            'password_confirmation.same' => 'পাসওয়ার্ড দুটি মিলছে না, আবার চেক করুন।',
        ]);

        $otp = (string) rand(1000, 9999);
        $email = Str::lower(trim($validated['email']));

        // ১০ মিনিটের জন্য Cache-এ রাখছি
        Cache::put("pending_register_{$email}", [
            'name' => trim(ucwords($validated['name'])),
            'email' => $email,
            'password' => $validated['password'],
            'otp' => $otp,
        ], now()->addMinutes(10));

        try {
            Mail::to($email)->send(new OtpMail($otp));
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'ইমেইল পাঠাতে সমস্যা হচ্ছে। পরে আবার চেষ্টা করুন।',
            ], 500);
        }

        return response()->json([
            'message' => 'আপনার ইমেইলে ৪ ডিজিটের একটি কোড পাঠানো হয়েছে।',
            'email' => $email,
        ]);
    }

    public function verifyAndRegister(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'string', 'size:4'],
        ], [
            'otp.required' => 'ভেরিফিকেশন কোড দিন।',
            'otp.size' => 'কোডটি ৪ ডিজিটের হতে হবে।',
        ]);

        $email = Str::lower(trim($request->email));
        $pending = Cache::get("pending_register_{$email}");

        if (!$pending) {
            throw ValidationException::withMessages([
                'otp' => ['ওটিপির মেয়াদ শেষ। পুনরায় চেষ্টা করুন।'],
            ]);
        }

        if ($request->otp !== $pending['otp']) {
            throw ValidationException::withMessages([
                'otp' => ['ভেরিফিকেশন কোডটি সঠিক নয়।'],
            ]);
        }

        try {
            $user = DB::transaction(function () use ($pending) {
                $user = User::create([
                    'name' => $pending['name'],
                    'email' => $pending['email'],
                    'password' => Hash::make($pending['password']),
                    'email_verified_at' => now(),
                ]);

                // Role assign (Spatie Permission থাকলে)
                if (method_exists($user, 'assignRole')) {
                    $user->assignRole('user');
                }

                return $user;
            });

            Cache::forget("pending_register_{$email}");

            // Token তৈরি (Sanctum)
            $token = $user->createToken('auth_token')->plainTextToken;

            // --- Multi-user cookie logic (optional for API) ---
            // Frontend থেকে cookie ম্যানেজ করা ভালো। Backend থেকে চাইলে:
            // Cookie::queue(...) ইত্যাদি

            return response()->json([
                'message' => 'অ্যাকাউন্ট সফলভাবে তৈরি হয়েছে।',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'token' => $token,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'অ্যাকাউন্ট তৈরিতে কারিগরি সমস্যা হয়েছে।',
            ], 500);
        }
    }

    // Optional: Resend OTP
    public function resendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $email = Str::lower(trim($request->email));
        $pending = Cache::get("pending_register_{$email}");

        if (!$pending) {
            return response()->json(['message' => 'কোনো pending রেজিস্ট্রেশন পাওয়া যায়নি।'], 404);
        }

        $otp = (string) rand(1000, 9999);
        $pending['otp'] = $otp;

        Cache::put("pending_register_{$email}", $pending, now()->addMinutes(10));

        Mail::to($email)->send(new OtpMail($otp));

        return response()->json(['message' => 'নতুন কোড পাঠানো হয়েছে।']);
    }
}