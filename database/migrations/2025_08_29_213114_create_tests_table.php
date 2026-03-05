<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tests', function (Blueprint $table) {
            $table->id();
            $table->string('title');

            // Foreign keys with shorthand
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_level_id')->constrained()->cascadeOnDelete();

            // Numeric fields
            $table->unsignedInteger('total_questions');
            $table->unsignedInteger('total_marks');
            $table->unsignedInteger('duration'); // in minutes

            // Optional datetime fields
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();

            $table->boolean('is_published')->default(false);

            // SEO fields
            $table->string('slug')->unique();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();

            // Audit fields
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('published_by')->nullable();

            // Misc fields
            $table->unsignedInteger('view_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('published_by')->references('id')->on('users')->nullOnDelete();

            // --- ADVANCED INDEXING (Maximum Efficiency) ---

            // ১. নির্দিষ্ট ক্লাসের নির্দিষ্ট সাবজেক্টের পাবলিশড পরীক্ষাগুলো দ্রুত পাওয়ার জন্য
            // WHERE class_level_id = ? AND subject_id = ? AND is_published = 1
            $table->index(['class_level_id', 'subject_id', 'is_published'], 'idx_test_lookup');

            // ২. চলমান পরীক্ষা (Running Exams) ফিল্টার করার জন্য
            // WHERE start_time <= NOW() AND end_time >= NOW()
            $table->index(['start_time', 'end_time', 'is_published'], 'idx_test_schedule');

            // ৩. স্ট্যাটাস এবং ফিচারড সর্টিং (টপ পরীক্ষাগুলো দেখানোর জন্য)
            $table->index(['status', 'is_featured', 'view_count'], 'idx_test_featured_stats');

            // ৪. পাবলিশ ডেট এবং টাইটেল দিয়ে সার্চের জন্য
            $table->index(['is_published', 'published_at']);
            $table->index('title');

            // ৫. সফট ডিলিট অপ্টিমাইজেশন
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tests');
    }
};