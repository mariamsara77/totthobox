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
        Schema::create('food_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name_bn');
            $table->string('name_en')->nullable();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();

            // Tracking / SEO / Audit
            $table->tinyInteger('status')->default(0);
            $table->string('image')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();

            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('view_count')->default(0);
            $table->boolean('is_featured')->default(false);

            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // --- ADVANCED INDEXING (Performance Optimization) ---

            // ১. একটিভ এবং ফিচারড ক্যাটাগরি দ্রুত পাওয়ার জন্য (হোমপেজ বা মেনু অপ্টিমাইজেশন)
            // WHERE status = 1 AND is_featured = 1
            $table->index(['status', 'is_featured'], 'idx_food_cat_status_featured');

            // ২. সার্চিং পারফরম্যান্স (বাংলা এবং ইংরেজি নাম দিয়ে সার্চ করার জন্য)
            $table->index('name_bn');
            $table->index('name_en');

            // ৩. পাবলিশ ডেট এবং ভিউ কাউন্ট সর্টিং (জনপ্রিয় বা লেটেস্ট ক্যাটাগরির জন্য)
            $table->index(['status', 'published_at', 'view_count'], 'idx_food_cat_popular');

            // ৪. সফট ডিলিট অপ্টিমাইজেশন
            $table->index('deleted_at');

            // ৫. ইউজার ভিত্তিক ডেটা অডিট করার জন্য
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_categories');
    }
};