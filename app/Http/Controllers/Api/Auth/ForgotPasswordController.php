<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'email'],
        ], [
            'email.required' => 'ইমেইল ঠিকানা দিতে হবে।',
            'email.email'    => 'সঠিক ইমেইল ফরম্যাট ব্যবহার করুন।',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'আপনার ইমেইলে পাসওয়ার্ড রিসেট লিংক পাঠানো হয়েছে।',
            ]);
        }

        // ইউজার না পাওয়া গেলে
        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }
}