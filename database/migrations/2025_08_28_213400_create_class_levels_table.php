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
        Schema::create('class_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique(); // Unique index অটোমেটিক তৈরি হয়
            $table->integer('order')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('status')->nullable();

            // SEO fields
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();

            // Audit fields
            $table->unsignedBigInteger('user_id')->nullable();
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

            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('published_by')->references('id')->on('users')->onDelete('set null');

            // --- ADVANCED INDEXING (Performance Optimization) ---

            // ১. একটিভ ক্লাস লেভেল এবং সর্টিং এর জন্য (মেনু বা ড্রপডাউনের জন্য)
            // এটি WHERE is_active = 1 ORDER BY order ASC কোয়েরিকে সুপার ফাস্ট করবে
            $table->index(['is_active', 'order'], 'idx_class_active_order');

            // ২. ফিচারড এবং স্ট্যাটাস ফিল্টারিং
            $table->index(['is_featured', 'status']);

            // ৩. সফট ডিলিট এবং টাইমস্ট্যাম্প অপ্টিমাইজেশন
            $table->index(['deleted_at', 'is_active']);

            // ৪. ইউজার এ্যাক্টিভিটি ট্র্যাক করার জন্য
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_levels');
    }
};