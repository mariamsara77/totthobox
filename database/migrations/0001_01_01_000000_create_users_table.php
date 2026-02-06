<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // Basic Info
            $table->string('name');
            $table->string('slug')->unique()->index(); // Index for faster profile lookups
            $table->string('email')->unique();
            $table->string('phone')->nullable()->unique()->index(); // Unique & Indexed for login/search
            $table->string('password');

            // Role & Permissions
            $table->enum('role', [
                'User',
                'Admin',
                'Super Admin',
                'Editor',
                'Creator',
                'Viewer',
                'Guest',
                'Moderator',
                'Contributor',
                'Analyst',
                'Custom'
            ])->default('User')->index();

            // Student Status (Added as per request)
            $table->boolean('is_student')->default(false)->index();
            $table->unsignedBigInteger('class_level_id')->nullable()->index();

            // Profile Media
            $table->string('avatar')->nullable();
            $table->string('google_id')->nullable()->unique();

            // Personal & Identity
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable()->index();
            $table->date('dob')->nullable();
            $table->string('nid', 20)->nullable()->unique(); // Length optimized
            $table->string('passport', 20)->nullable()->unique();
            $table->string('blood_group', 5)->nullable();

            // Location Data (Essential for filtering)
            $table->foreignId('division_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('thana_id')->nullable()->constrained()->nullOnDelete();
            $table->string('location')->nullable(); // Short location/city
            $table->text('address')->nullable(); // Detail address

            // Demographics & Professional
            $table->string('profession')->nullable()->index();
            $table->string('occupation')->nullable();
            $table->string('education')->nullable();
            $table->string('nationality')->default('Bangladeshi');
            $table->string('religion')->nullable();
            $table->string('marital_status')->nullable();
            $table->decimal('height', 4, 2)->nullable(); // e.g. 5.11
            $table->decimal('weight', 5, 2)->nullable(); // e.g. 110.50

            // Financial Info
            $table->string('annual_income')->nullable();
            $table->string('monthly_income')->nullable();

            // Bio & Details
            $table->text('bio')->nullable();
            $table->text('note')->nullable();
            $table->string('status')->default('active')->index(); // Status index essential for global filtering

            // System Timestamps
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_active_at')->nullable()->index();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            // Multi-column Index for Performance
            $table->index(['status', 'role', 'is_student']);
        });

        // -----------------------------
        // Other Tables (Reset & Session)
        // -----------------------------
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};