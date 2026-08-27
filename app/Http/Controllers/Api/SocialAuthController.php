<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function handleGoogleCallback(Request $request)
    {
        $request->validate([
            'access_token' => 'required|string',
        ]);

        try {
            $googleUser = Socialite::driver('google')->userFromToken($request->access_token);

            $user = User::updateOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'email_verified_at' => now(),
                    'password' => bcrypt(Str::random(16)),
                ]
            );

            $token = $user->createToken('google-auth-token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid Google Token'], 401);
        }
    }
}