<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Str;

class GoogleLoginController extends Controller
{
    public function redirectToGoogle()
    {
        return response()->json([
            'message' => 'Use the current Google authentication endpoint.',
        ], 410);
    }
}
