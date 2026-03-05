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
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_level_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('slug')->unique();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status')->nullable();

            // SEO fields
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();

            // Audit fields
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('published_by')->nullable();

            $table->integer('view_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Additional Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('published_by')->references('id')->on('users')->onDelete('set null');

            // --- ADVANCED INDEXING (Maximum Performance) ---

            // ১. রিলেশনাল কোয়েরি অপ্টিমাইজেশন (নির্দিষ্ট ক্লাসের একটিভ সাবজেক্ট দ্রুত পাওয়ার জন্য)
            // এটি WHERE class_level_id = ? AND is_active = 1 কোয়েরিকে সুপার ফাস্ট করবে
            $table->index(['class_level_id', 'is_active'], 'idx_subject_class_active');

            // ২. ফিচারড এবং ভিউ কাউন্ট সর্টিং
            // হোমে বা লিস্টে জনপ্রিয় সাবজেক্ট দেখানোর জন্য
            $table->index(['is_featured', 'is_active', 'view_count'], 'idx_subject_featured_stats');

            // ৩. পাবলিশ ডেট এবং স্ট্যাটাস ইনডেক্স
            $table->index(['status', 'published_at']);

            // ৪. সফট ডিলিট এবং সার্চ অপ্টিমাইজেশন
            $table->index(['deleted_at', 'is_active']);

            // ৫. নাম ভিত্তিক সার্চের জন্য
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};