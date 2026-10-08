<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Auth\GoogleAuthController;
use App\Models\User;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleLoginController extends Controller
{
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')
            ->stateless()
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function handleCallback(): RedirectResponse
    {
        $frontendUrl = rtrim(
            config('app.frontend_url', env('FRONTEND_URL', 'https://totthobox.com')),
            '/'
        );

        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->user();

            $googleId = trim((string) $googleUser->getId());
            $email = strtolower(trim((string) $googleUser->getEmail()));

            if ($googleId === '' || $email === '') {
                throw new Exception('Google did not return a valid identity.');
            }

            $user = DB::transaction(function () use ($googleUser, $googleId, $email) {
                $user = User::where('google_id', $googleId)->first();

                if (! $user) {
                    $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
                }

                if ($user) {
                    $user->forceFill([
                        'google_id' => $user->google_id ?: $googleId,
                        'email_verified_at' => $user->email_verified_at ?? now(),
                        'status' => $user->status ?: 'active',
                    ])->save();

                    return $user;
                }

                $user = User::create([
                    'name' => $googleUser->getName() ?: 'Google User',
                    'email' => $email,
                    'google_id' => $googleId,
                    'password' => Hash::make(Str::random(64)),
                    'email_verified_at' => now(),
                    'status' => 'active',
                ]);

                if (method_exists($user, 'assignRole')) {
                    $user->assignRole('user');
                }

                return $user;
            });

            $code = GoogleAuthController::issueExchangeCode($user);

            return redirect()->away(
                $frontendUrl . '/auth/callback?' .
                http_build_query(['code' => $code])
            );
        } catch (Exception $e) {
            report($e);

            return redirect()->away(
                $frontendUrl . '/auth/callback?error=google_auth_failed'
            );
        }
    }
}
