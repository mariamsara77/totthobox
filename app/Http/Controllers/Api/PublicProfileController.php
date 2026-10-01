<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PublicUserResource;
use App\Models\User;

class PublicProfileController extends Controller
{
    public function show(string $slug)
    {
        $user = User::where('slug', $slug)
            ->with(['classLevel', 'division', 'district', 'thana', 'roles'])
            ->firstOrFail();

        return new PublicUserResource($user);
    }
}