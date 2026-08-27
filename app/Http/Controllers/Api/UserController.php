<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    /**
     * Get Authenticated User Info
     *
     * Existing API:
     * GET /api/user
     */
    public function getUser(Request $request)
    {
        /** @var \App\Models\User|null $user */
        $user = $request->user();

        if (!$user) {
            return response()
                ->json([
                    'message' => 'Unauthenticated.',
                ], 401)
                ->header('Cache-Control', 'no-store');
        }

        if (method_exists($user, 'initials')) {
            $user->setAttribute(
                'initials',
                $user->initials()
            );
        }

        if (method_exists($user, 'isOnline')) {
            $user->setAttribute(
                'is_online',
                $user->isOnline()
            );
        }

        $user->loadMissing([
            'division',
            'district',
            'thana',
        ]);

        return response()
            ->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'slug' => $user->slug,
                'avatar_url' => $user->avatar_url,

                'initials' => $user->initials ?? null,
                'is_online' => $user->is_online ?? false,

                'division' => $user->division,
                'district' => $user->district,
                'thana' => $user->thana,
            ], 200)
            ->header(
                'Cache-Control',
                'private, no-store, no-cache, must-revalidate, max-age=0'
            )
            ->header('Pragma', 'no-cache');
    }


    /**
     * Toggle Block / Unblock User
     *
     * POST /api/users/{user}/block
     */
    public function toggleBlock(Request $request, User $user)
    {
        /** @var User|null $authUser */
        $authUser = $request->user();

        if (!$authUser) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Cannot block yourself
        if ($authUser->id === $user->id) {
            return response()->json([
                'message' => 'You cannot block yourself.',
            ], 422);
        }

        /*
         * IMPORTANT:
         *
         * এখানে তোমার existing block storage logic বসবে।
         *
         * User model-এ বর্তমানে block সম্পর্কিত কোনো field
         * বা relationship নেই।
         *
         * তাই এখানে নতুন UserBlock model ধরে নেওয়া হচ্ছে না।
         */

        return response()->json([
            'success' => true,
            'message' => 'Block system is not configured yet.',
        ], 501);
    }


    /**
     * Get Block Status
     *
     * GET /api/users/{user}/block-status
     */
    public function blockStatus(Request $request, User $user)
    {
        /** @var User|null $authUser */
        $authUser = $request->user();

        if (!$authUser) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        /*
         * User model-এ বর্তমানে block সম্পর্কিত কোনো
         * field / relationship নেই।
         */

        return response()->json([
            'success' => true,
            'blocked' => false,
        ], 200);
    }

    public function search(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $search = trim($request->input('search', ''));

        $users = User::query()
            ->whereKeyNot(auth()->id())
            ->with(['roles', 'media'])
            ->whereDoesntHave('roles', function ($query) {
                $query->whereIn('name', ['Admin', 'Super Admin']);
            })
            ->when(filled($search), function ($query) use ($search) {
                $query->whereAny(
                    ['name', 'email', 'phone', 'slug'],
                    'like',
                    '%' . $search . '%'
                );
            })
            // MariaDB / MySQL nulls-last ordering
            ->orderByRaw('last_active_at IS NULL ASC')
            ->orderByDesc('last_active_at')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => UserResource::collection($users), // or just $users if you don't use Resource
        ]);
    }
}