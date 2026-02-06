<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Register
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create([
            'name' => $data['name'],
            'email'=> $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Create token
        $token = $user->createToken($request->device_name ?? 'mobile')->plainTextToken;

        return response()->json([
            'message' => 'User registered',
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    // Login with rate-limiting
    public function login(LoginRequest $request): JsonResponse
    {
        $this->throttle($request);

        $credentials = $request->only('email', 'password');

        if (!Auth::attempt($credentials)) {
            // increment attempts
            RateLimiter::hit($this->throttleKey($request), 60);
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));

        /** @var User $user */
        $user = Auth::user();

        // recreate token for device
        $user->tokens()->where('name', $request->device_name ?? 'mobile')->delete();
        $token = $user->createToken($request->device_name ?? 'mobile')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => $user,
            'token' => $token,
        ]);
    }

    // Logout: revoke current token
    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out']);
    }

    // Profile
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    // Helpers
    protected function throttle(Request $request)
    {
        $key = $this->throttleKey($request);
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => ["Too many attempts. Try again in $seconds seconds."],
            ]);
        }
    }

    protected function throttleKey(Request $request): string
    {
        return strtolower($request->input('email')).'|'.$request->ip();
    }
}
