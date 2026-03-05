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
        Schema::create('buy_sell_categories', function (Blueprint $table) {
            // Primary Key
            $table->id();

            // Basic Information
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->string('note')->nullable();
            $table->integer('order')->default(0); // ক্যাটাগরি সাজানোর জন্য

            // Status & Visibility
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');

            // Publishing
            $table->timestamp('published_at')->nullable();

            // SEO Fields
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();

            // Analytics
            $table->unsignedBigInteger('view_count')->default(0);

            // Audit Fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();

            // Technical Fields
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // --- ADVANCED INDEXING (Maximum Efficiency) ---

            // ১. নেভিগেশন ও মেনু সর্টিং এর জন্য (সবচেয়ে বেশি ব্যবহৃত হবে)
            // এটি WHERE is_active = 1 AND status = 'published' ORDER BY order ASC কোডকে অপ্টিমাইজ করবে
            $table->index(['is_active', 'status', 'order'], 'idx_buy_sell_cat_nav');

            // ২. হোমপেজ বা ফিচারড ক্যাটাগরির জন্য
            $table->index(['is_featured', 'is_active', 'status'], 'idx_buy_sell_cat_featured');

            // ৩. নাম ভিত্তিক সার্চ অপ্টিমাইজেশন
            $table->index('name');

            // ৪. জনপ্রিয় ক্যাটাগরি সর্টিং (Analytics based)
            $table->index(['view_count', 'status'], 'idx_buy_sell_cat_popular');

            // ৫. অডিট এবং ডিলিট ট্র্যাকিং
            $table->index('deleted_at');
            $table->index('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buy_sell_categories');
    }
};