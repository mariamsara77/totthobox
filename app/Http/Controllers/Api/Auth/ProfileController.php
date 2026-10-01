<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserProfileResource;
use App\Models\ClassLevel;
use App\Models\District;
use App\Models\Division;
use App\Models\Thana;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user()->load(['classLevel', 'division', 'district', 'thana', 'roles']);

        return new UserProfileResource($user);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'email'          => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'profession'     => ['nullable', 'string', 'max:255'],
            'bio'            => ['nullable', 'string'],
            'location'       => ['nullable', 'string', 'max:255'],
            'division_id'    => ['nullable', 'exists:divisions,id'],
            'district_id'    => ['nullable', 'exists:districts,id'],
            'thana_id'       => ['nullable', 'exists:thanas,id'],
            'class_level_id' => [
                Rule::requiredIf(fn () => strtolower($request->selected_role ?? '') === 'student'),
                'nullable',
                'exists:class_levels,id',
            ],
            'selected_role'  => ['required', 'string'],
            'avatar'         => ['nullable', 'image', 'max:2048'],
        ]);

        // Role permission check (same logic as your Livewire)
        $availableRoles = $this->getAvailableRoles($user);
        if (!in_array($validated['selected_role'], $availableRoles)) {
            return response()->json(['message' => 'You are not allowed to select this role.'], 403);
        }

        $user->update([
            'name'           => $validated['name'],
            'email'          => $validated['email'],
            'profession'     => $validated['profession'] ?? null,
            'bio'            => $validated['bio'] ?? null,
            'location'       => $validated['location'] ?? null,
            'division_id'    => $validated['division_id'] ?? null,
            'district_id'    => $validated['district_id'] ?? null,
            'thana_id'        => $validated['thana_id'] ?? null,
            'class_level_id' => strtolower($validated['selected_role']) === 'student'
                ? ($validated['class_level_id'] ?? null)
                : null,
        ]);

        // Sync role
        if ($user->getRoleNames()->first() !== $validated['selected_role']) {
            $user->syncRoles([$validated['selected_role'] ?: 'user']);
        }

        // Avatar
        if ($request->hasFile('avatar')) {
            $user->clearMediaCollection('avatars');
            $user->addMediaFromRequest('avatar')
                ->usingFileName(Str::slug($user->name) . '-' . time() . '.' . $request->file('avatar')->getClientOriginalExtension())
                ->toMediaCollection('avatars');
        }

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user'    => new UserProfileResource($user->fresh()->load(['classLevel', 'division', 'district', 'thana', 'roles'])),
        ]);
    }

    public function updateAvatar(Request $request)
    {
        $request->validate(['avatar' => ['required', 'image', 'max:2048']]);

        $user = Auth::user();
        $user->clearMediaCollection('avatars');

        $user->addMediaFromRequest('avatar')
            ->usingFileName(Str::slug($user->name) . '-' . time() . '.' . $request->file('avatar')->getClientOriginalExtension())
            ->toMediaCollection('avatars');

        return response()->json([
            'message'    => 'Avatar updated.',
            'avatar_url' => $user->fresh()->getFirstMediaUrl('avatars', 'thumb'),
        ]);
    }

    public function removeAvatar()
    {
        $user = Auth::user();
        $user->clearMediaCollection('avatars');
        $user->update(['avatar' => null]);

        return response()->json(['message' => 'Avatar removed.']);
    }

    public function removeRole()
    {
        $user = Auth::user();
        $user->syncRoles(['user']);

        return response()->json([
            'message' => 'Role removed.',
            'user'    => new UserProfileResource($user->fresh()->load('roles')),
        ]);
    }

    public function availableRoles()
    {
        return response()->json([
            'roles' => $this->getAvailableRoles(Auth::user()),
        ]);
    }

    // Location helpers
    public function divisions()
    {
        return Division::select('id', 'name')->get();
    }

    public function districts($divisionId)
    {
        return District::where('division_id', $divisionId)->select('id', 'name')->get();
    }

    public function thanas($districtId)
    {
        return Thana::where('district_id', $districtId)->select('id', 'name')->get();
    }

    public function classLevels()
    {
        return ClassLevel::select('id', 'name')->get();
    }

    // ---- private helper (same logic as your Livewire mount) ----
    private function getAvailableRoles(User $user): array
    {
        $selected = $user->getRoleNames()->first() ?? 'user';

        return Role::pluck('name')
            ->filter(function ($roleName) use ($user, $selected) {
                if (strtolower($roleName) === 'user') {
                    return true;
                }
                if ($selected === $roleName) {
                    return true;
                }
                $permissionName = 'assign ' . strtolower($roleName);
                return Permission::where('name', $permissionName)->exists()
                    && $user->hasPermissionTo($permissionName);
            })
            ->unique()
            ->values()
            ->toArray();
    }
}