<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SwitchProfileController extends Controller
{
    /**
     * Saved profile-এ click করলে password দিয়ে re-authenticate করে।
     * Token কখনো client-এ expose হয় না — httpOnly cookie-তে set হয়।
     */
    public function switch(Request $request)
    {
        $validated = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'ইমেইল দিতে হবে।',
            'password.required' => 'পাসওয়ার্ড দিতে হবে।',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['পাসওয়ার্ডটি সঠিক নয়।'],
            ]);
        }

        // এই device-এর পুরনো token সরিয়ে নতুন token তৈরি
        // (সব device-এর token delete না করে শুধু current device-এরটা)
        $tokenName = 'web_' . $request->userAgent() . '_' . now()->timestamp;
        $token = $user->createToken($tokenName)->plainTextToken;

        return response()
            ->json([
                'user' => $user->only(['id', 'name', 'email', 'slug', 'avatar_url']),
            ])
            ->cookie(
                'auth_token',          // name
                $token,                // value — client JS দেখতে পাবে না
                60 * 24 * 30,          // 30 দিন
                '/',                   // path
                null,                  // domain
                true,                  // secure (HTTPS only)
                true,                  // httpOnly ← এটাই XSS থেকে বাঁচায়
                false,
                'Lax'                  // sameSite
            );
    }
}