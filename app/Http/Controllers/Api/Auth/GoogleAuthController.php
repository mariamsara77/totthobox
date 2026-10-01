<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class GoogleAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate(['access_token' => ['required', 'string']]);

        try {
            $googleResponse = Http::timeout(5)->get('https://www.googleapis.com/oauth2/v3/userinfo', [
                'access_token' => $request->access_token,
            ]);

            if ($googleResponse->failed()) {
                return response()->json(['message' => 'Invalid or expired Google token.'], 401);
            }

            $payload = $googleResponse->json();

            if (empty($payload['email_verified'])) {
                return response()->json(['message' => 'Unverified Google account.'], 403);
            }

            $user = DB::transaction(function () use ($payload) {
                $user = User::where('email', $payload['email'])->first();

                if ($user) {
                    // বিদ্যমান ইউজার
                    if (empty($user->google_id)) {
                        $user->update(['google_id' => $payload['sub']]);
                    }

                    return $user;
                }

                // নতুন ইউজার
                $user = User::create([
                    'name'              => $payload['name'] ?? 'User',
                    'email'             => $payload['email'],
                    'google_id'         => $payload['sub'],
                    'avatar'            => $payload['picture'] ?? null,
                    'email_verified_at' => now(),
                    'password'          => bcrypt(Str::random(32)),
                ]);

                $user->assignRole('user');   // ← এখানেই রাখুন

                return $user;
            });

            return response()->json([
                'token' => $user->createToken('auth_token')->plainTextToken,
                'user' => $user->only(['id', 'name', 'email', 'slug', 'avatar_url']),
            ]);
        } catch (Throwable $e) {
            Log::error('Google Auth Error: ' . $e->getMessage());

            return response()->json(['message' => 'Authentication failed due to server error.'], 500);
        }
    }
}