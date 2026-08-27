<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
  public function me(Request $request)
{
    return response()->json([
        'user' => $request->user(),
    ]);
}

public function logout(Request $request)
{
    $user = $request->user();

    if ($user && $user->currentAccessToken()) {
        $user->currentAccessToken()->delete();
    }

    return response()->json([
        'success' => true,
        'message' => 'লগআউট সফল হয়েছে।',
    ]);
}
}