<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleLoginController extends Controller
{
    public function redirectToGoogle()
    {
        $url = Socialite::driver('google')
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return response()->json([
            'url' => $url,
        ]);
    }

    public function handleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->user();

            $user = $this->findOrCreateUser([
                'google_id'      => $googleUser->getId(),
                'email'          => $googleUser->getEmail(),
                'name'           => $googleUser->getName(),
                'avatar'         => $googleUser->getAvatar(),
                'email_verified' => true,
            ]);

            $user->tokens()
                ->where('name', 'google-auth')
                ->delete();

            $token = $user
                ->createToken('google-auth')
                ->plainTextToken;

            $frontendUrl = config(
                'app.frontend_url',
                'https://totthobox.com'
            );

            return redirect()->away(
                $frontendUrl .
                '/auth/callback?token=' .
                urlencode($token)
            );

        } catch (\Throwable $e) {

            Log::error('Google Socialite Callback Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $frontendUrl = config(
                'app.frontend_url',
                'https://totthobox.com'
            );

            return redirect()->away(
                $frontendUrl .
                '/auth/error?message=google_auth_failed'
            );
        }
    }

    private function findOrCreateUser(array $data): User
    {
        $user = User::where('email', $data['email'])->first();

        if ($user) {
            $user->forceFill([
                'google_id' => $data['google_id'],
                'avatar' => $data['avatar'],
                'email_verified_at' => now(),
            ])->save();

            return $user;
        }

        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'google_id' => $data['google_id'],
            'avatar' => $data['avatar'],
            'email_verified_at' => now(),
            'password' => Hash::make(Str::random(40)),
        ]);
    }
}