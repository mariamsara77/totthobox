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
        Schema::create('user_test_attempts', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('test_id')->constrained()->cascadeOnDelete();

            // Attempt details
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->unsignedInteger('score')->nullable();
            $table->unsignedInteger('correct_answers')->nullable();
            $table->unsignedInteger('wrong_answers')->nullable();
            $table->json('answers')->nullable();

            // SEO & general fields
            $table->string('slug')->unique();
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

            // Foreign key constraints for audit fields
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('published_by')->references('id')->on('users')->nullOnDelete();

            // --- ADVANCED INDEXING (Performance Boost) ---

            // ১. লিডারবোর্ড এবং নির্দিষ্ট পরীক্ষার রেজাল্ট দ্রুত দেখার জন্য
            // WHERE test_id = ? ORDER BY score DESC
            $table->index(['test_id', 'score', 'completed_at'], 'idx_test_leaderboard');

            // ২. ইউজারের নিজস্ব পরীক্ষার হিস্ট্রি দ্রুত লোড করার জন্য
            // WHERE user_id = ? ORDER BY started_at DESC
            $table->index(['user_id', 'started_at'], 'idx_user_attempt_history');

            // ৩. স্ট্যাটাস ভিত্তিক ফিল্টারিং (যেমন: কতজন 'completed' করেছে)
            $table->index(['status', 'completed_at']);

            // ৪. ডেট রেঞ্জ সার্চ (যেমন: আজকের কতজন পরীক্ষা দিল)
            $table->index('started_at');

            // ৫. সফট ডিলিট অপ্টিমাইজেশন
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_test_attempts');
    }
};