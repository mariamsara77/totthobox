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
            $table->string('slug')->unique();
            $table->string('email')->unique();
            $table->string('phone')->nullable()->unique();
            $table->string('password');
            
            // Student Status
            $table->boolean('is_student')->default(false);
            $table->unsignedBigInteger('class_level_id')->nullable();

            // Profile Media
            $table->string('avatar')->nullable();
            $table->string('google_id')->nullable()->unique();

            // Personal & Identity
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
            $table->date('dob')->nullable();
            $table->string('nid', 20)->nullable()->unique();
            $table->string('passport', 20)->nullable()->unique();
            $table->string('blood_group', 5)->nullable();

            // Location Data
            $table->unsignedBigInteger('division_id')->nullable()->index();
            $table->unsignedBigInteger('district_id')->nullable()->index();
            $table->unsignedBigInteger('thana_id')->nullable()->index();
            $table->string('location')->nullable();
            $table->text('address')->nullable();

            // Demographics & Professional
            $table->string('profession')->nullable();
            $table->string('occupation')->nullable();
            $table->string('education')->nullable();
            $table->string('nationality')->default('Bangladeshi');
            $table->string('religion')->nullable();
            $table->string('marital_status')->nullable();
            $table->decimal('height', 4, 2)->nullable();
            $table->decimal('weight', 5, 2)->nullable();

            // Financial Info
            $table->string('annual_income')->nullable();
            $table->string('monthly_income')->nullable();

            // Bio & Details
            $table->text('bio')->nullable();
            $table->text('note')->nullable();
            $table->string('status')->default('active');

            // System Timestamps
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            // --- ADVANCED INDEXING (Maximum Efficiency) ---

            // ১. লগইন অপ্টিমাইজেশন (Email/Phone + Password check is internal, but lookup needs to be fast)
            $table->index(['email', 'status'], 'idx_user_auth_email');
            $table->index(['phone', 'status'], 'idx_user_auth_phone');

            // ২. অ্যাডমিন প্যানেল ফিল্টারিং (Role, Status, and Student status combination)
            $table->index(['status', 'is_student', 'created_at'], 'idx_user_admin_listing');

            // ৩. লোকেশন ভিত্তিক ইউজার সার্চ (যেমন: নির্দিষ্ট জেলার সব স্টুডেন্ট খোঁজা)
            $table->index(['district_id', 'status', 'is_student'], 'idx_user_geo_search');
            $table->index(['division_id', 'status'], 'idx_user_division_filter');

            // ৪. প্রফেশনাল এবং সোশ্যাল ফিল্টার
            $table->index(['profession', 'status'], 'idx_user_profession');
            $table->index(['gender', 'status', 'blood_group'], 'idx_user_demographics');

            // ৫. অ্যাক্টিভিটি ট্র্যাকিং (অনলাইন ইউজারদের দ্রুত খুঁজে পেতে)
            $table->index(['last_active_at', 'status'], 'idx_user_online_status');

            // ৬. সফট ডিলিট অপ্টিমাইজেশন
            $table->index('deleted_at');
        });

        // -----------------------------
        // Other Tables
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