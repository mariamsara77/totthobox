<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Get Authenticated User Info
     */
    public function getUser(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Extra attributes (আপনার Model-এ method থাকলে)
        if (method_exists($user, 'initials')) {
            $user->setAttribute('initials', $user->initials());
        }

        if (method_exists($user, 'isOnline')) {
            $user->setAttribute('is_online', $user->isOnline());
        }

        $user->loadMissing(['division', 'district', 'thana']);

        // Frontend User interface-এর সাথে মিল রাখতে
        return response()->json([
            'id'         => $user->id,
            'name'       => $user->name,
            'email'      => $user->email,
            'slug'       => $user->slug ?? null,
            'avatar_url' => $user->avatar_url ?? null,
            // নিচেরগুলো অতিরিক্ত (Frontend-এ দরকার হলে রাখুন)
            'initials'   => $user->initials ?? null,
            'is_online'  => $user->is_online ?? false,
            'division'   => $user->division ?? null,
            'district'   => $user->district ?? null,
            'thana'      => $user->thana ?? null,
        ], 200);
    }
}