<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleLoginController extends Controller
{
    /**
     * Generate the Google OAuth authorization URL.
     *
     * OAuth state is generated server-side and stored in Redis.
     */
    public function redirectToGoogle()
    {
        try {
            $state = Str::random(64);

            Cache::put(
                $this->stateKey($state),
                [
                    'created_at' => now()->timestamp,
                ],
                now()->addMinutes(10)
            );

            $googleConfig = config('services.google');

            $query = http_build_query([
                'client_id' => $googleConfig['client_id'],
                'redirect_uri' => $googleConfig['redirect'],
                'response_type' => 'code',
                'scope' => 'openid profile email',
                'state' => $state,
                'prompt' => 'select_account',
                'access_type' => 'online',
            ]);

            $url = 'https://accounts.google.com/o/oauth2/v2/auth?' . $query;

            return response()->json([
                'url' => $url,
            ]);
        } catch (\Throwable $e) {
            Log::error('Google OAuth Redirect Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'message' => 'Google লগইন শুরু করা যায়নি।',
            ], 500);
        }
    }

    /**
     * Handle Google OAuth callback.
     *
     * Google -> Laravel -> one-time exchange code -> Next.js.
     *
     * The real Sanctum token is NEVER exposed in the browser URL.
     */
    public function handleCallback(Request $request)
    {
        $frontendUrl = rtrim(
            config('app.frontend_url', 'https://totthobox.com'),
            '/'
        );

        try {
            /*
             * -------------------------------------------------------------
             * 1. Validate OAuth state
             * -------------------------------------------------------------
             */

            $state = (string) $request->query('state', '');

            if ($state === '') {
                Log::warning('Google OAuth callback missing state');

                return redirect()->away(
                    $frontendUrl . '/login?error=google-invalid-state'
                );
            }

            $stateData = Cache::pull(
                $this->stateKey($state)
            );

            if (!$stateData) {
                Log::warning('Google OAuth state expired or invalid');

                return redirect()->away(
                    $frontendUrl . '/login?error=google-state-expired'
                );
            }

            /*
             * -------------------------------------------------------------
             * 2. Validate Google authorization response
             * -------------------------------------------------------------
             */

            $googleError = $request->query('error');

            if ($googleError) {
                Log::info('Google OAuth cancelled or failed', [
                    'error' => $googleError,
                ]);

                return redirect()->away(
                    $frontendUrl . '/login?error=google-cancelled'
                );
            }

            $authorizationCode = (string) $request->query('code', '');

            if ($authorizationCode === '') {
                Log::warning('Google OAuth callback missing authorization code');

                return redirect()->away(
                    $frontendUrl . '/login?error=google-code-missing'
                );
            }

            /*
             * -------------------------------------------------------------
             * 3. Exchange Google authorization code using Socialite
             * -------------------------------------------------------------
             */

            $googleUser = Socialite::driver('google')
                ->stateless()
                ->user();

            $googleId = $googleUser->getId();
            $email = $googleUser->getEmail();

            if (!$googleId || !$email) {
                Log::warning(
                    'Google OAuth returned incomplete user data'
                );

                return redirect()->away(
                    $frontendUrl . '/login?error=google-user-invalid'
                );
            }

            /*
             * -------------------------------------------------------------
             * 4. Find or create local user
             * -------------------------------------------------------------
             */

            $user = $this->findOrCreateUser([
                'google_id' => $googleId,
                'email' => $email,
                'name' => $googleUser->getName() ?: $email,
                'avatar' => $googleUser->getAvatar(),
            ]);

            /*
             * -------------------------------------------------------------
             * 5. Remove old Google authentication tokens
             * -------------------------------------------------------------
             */

            $user->tokens()
                ->where('name', 'google-auth')
                ->delete();

            /*
             * -------------------------------------------------------------
             * 6. Create one-time exchange code
             * -------------------------------------------------------------
             */

            $code = Str::random(96);

            Cache::put(
                $this->exchangeCodeKey($code),
                [
                    'user_id' => $user->getKey(),
                    'created_at' => now()->timestamp,
                ],
                now()->addMinutes(2)
            );

            /*
             * -------------------------------------------------------------
             * 7. Redirect to Next.js
             * -------------------------------------------------------------
             *
             * ONLY the temporary exchange code is exposed.
             */

            return redirect()->away(
                $frontendUrl .
                '/auth/callback?code=' .
                rawurlencode($code)
            );
        } catch (\Throwable $e) {
            Log::error('Google Socialite Callback Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return redirect()->away(
                $frontendUrl . '/login?error=google-auth-failed'
            );
        }
    }

    /**
     * Exchange one-time authentication code for Sanctum token.
     */
    public function exchange(Request $request)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:128',
            ],
        ]);

        $code = trim($validated['code']);

        $exchangeData = Cache::pull(
            $this->exchangeCodeKey($code)
        );

        if (
            !$exchangeData ||
            empty($exchangeData['user_id'])
        ) {
            return response()->json([
                'message' =>
                    'Google authentication code অবৈধ অথবা মেয়াদ শেষ হয়েছে।',
            ], 401);
        }

        $user = User::find(
            $exchangeData['user_id']
        );

        if (!$user) {
            return response()->json([
                'message' => 'Authentication user পাওয়া যায়নি।',
            ], 401);
        }

        /*
         * Remove previous Google-auth tokens.
         */
        $user->tokens()
            ->where('name', 'google-auth')
            ->delete();

        /*
         * Create new Sanctum token.
         */
        $token = $user
            ->createToken('google-auth')
            ->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
        ]);
    }

    /**
     * Find an existing user by email or create a new Google user.
     */
    private function findOrCreateUser(array $data): User
    {
        $user = User::where(
            'email',
            $data['email']
        )->first();

        if ($user) {
            $user->forceFill([
                'google_id' => $data['google_id'],
                'avatar' => $data['avatar'],
                'email_verified_at' =>
                    $user->email_verified_at ?: now(),
            ])->save();

            return $user;
        }

        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'google_id' => $data['google_id'],
            'avatar' => $data['avatar'],
            'email_verified_at' => now(),
            'password' => Hash::make(
                Str::random(64)
            ),
        ]);
    }

    /**
     * Redis key for OAuth state.
     */
    private function stateKey(string $state): string
    {
        return 'totthobox:oauth:google:state:' .
            hash('sha256', $state);
    }

    /**
     * Redis key for one-time exchange code.
     */
    private function exchangeCodeKey(string $code): string
    {
        return 'totthobox:oauth:google:exchange:' .
            hash('sha256', $code);
    }
}