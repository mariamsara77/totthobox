<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use App\Models\RefreshToken;
use Illuminate\Validation\ValidationException;

class NewPasswordController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'token.required' => 'টোকেন পাওয়া যায়নি।',
            'email.required' => 'ইমেইল ঠিকানা দিতে হবে।',
            'email.email' => 'সঠিক ইমেইল ফরম্যাট ব্যবহার করুন।',
            'password.required' => 'নতুন পাসওয়ার্ড দিতে হবে।',
            'password.min' => 'পাসওয়ার্ড কমপক্ষে ৮ অক্ষরের হতে হবে।',
            'password.confirmed' => 'পাসওয়ার্ড মিলছে না।',
        ]);

        $user = null;

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $resetUser, $password) use (&$user) {
                $resetUser->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                // পাসওয়ার্ড রিসেট = security event, পুরনো সব সেশন invalidate করা standard practice
                $resetUser->tokens()->delete();

                event(new PasswordReset($resetUser));

                $user = $resetUser;
            }
        );

        if ($status === Password::PASSWORD_RESET && $user) {
            $fingerprint = substr(
                md5($request->userAgent() . $request->ip()),
                0,
                12
            );

            $accessToken = $user
                ->createToken('web_reset_' . $fingerprint)
                ->plainTextToken;

            $refreshToken = RefreshToken::issue($user, $fingerprint);

            return response()->json([
                'success' => true,
                'message' => __($status),
                'access_token' => $accessToken,
                'refresh_token' => $refreshToken,
                'user' => $user->only(['id', 'name', 'email', 'slug', 'avatar_url']),
            ]);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }
}