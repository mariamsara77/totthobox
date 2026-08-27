<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // ১. স্প্যাটি পারমিশন ক্যাশ ক্লিয়ার করা
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ২. সব পারমিশন ডিফাইন করা
        $permissions = [
            'view-dashboard',
            'manage-users',
            'manage-roles',
            'manage-bangladesh',
            'manage-islam',
            'manage-health',
            'manage-education',
            'manage-contacts',
            'manage-signs',
            'manage-buysell',
            'manage-excel',
        ];

        // ৩. পারমিশনগুলো তৈরি করা
        foreach ($permissions as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        // ৪. রোল তৈরি করা (Spatie Table: roles)
            $adminRole = Role::findOrCreate('admin', 'web');
            $userRole = Role::findOrCreate('user', 'web');

            // ৫. Admin রোলকে সব পারমিশন সিঙ্ক করে দেওয়া
        $adminRole->syncPermissions(Permission::all());

        // ৬. ইউজার তৈরি করা (Spatie-র বাইরে কোনো কলাম নেই)
        $adminUser = User::updateOrCreate(
            ['email' => 'mariamsara.freelancer@gmail.com'],
            [
                'name' => 'Mariam Sara',
                'slug' => 'mariam-sara-' . Str::lower(Str::random(5)),
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        // ৭. স্প্যাটি রিলেশন টেবিলে রোল অ্যাসাইন করা (Spatie Table: model_has_roles)
        if (!$adminUser->hasRole('admin')) {
            $adminUser->assignRole($adminRole);
        }

        $this->command->info('Success: Admin user created and Role assigned via Spatie!');
    }
}