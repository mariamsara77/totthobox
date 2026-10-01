<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string', 'current_password'],
        ], [
            'password.required'         => 'Password is required to delete account.',
            'password.current_password' => 'The provided password is incorrect.',
        ]);

        $user = Auth::user();

        // Optional: revoke all tokens
        $user->tokens()->delete();

        $user->delete(); // SoftDeletes is enabled

        return response()->json(['message' => 'Account deleted successfully.']);
    }
}