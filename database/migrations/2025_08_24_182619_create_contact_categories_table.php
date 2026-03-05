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
        Schema::create('contact_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);

            // Status as enum
            $table->enum('status', ['active', 'inactive'])->default('inactive');

            $table->json('extra_attributes')->nullable();

            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // --- ADVANCED INDEXING (Performance Boost) ---

            // ১. ক্যাটাগরি লিস্টিং এবং একটিভ চেক করার জন্য
            // WHERE is_active = 1 AND is_featured = 1
            $table->index(['is_active', 'is_featured'], 'idx_cat_active_featured');

            // ২. স্ট্যাটাস ভিত্তিক ফিল্টারিং (Enum কলামের জন্য ইনডেক্স)
            $table->index('status');

            // ৩. নাম দিয়ে সার্চ বা সর্টিং এর জন্য
            $table->index('name');

            // ৪. সফট ডিলিট এবং টাইমস্ট্যাম্প অপ্টিমাইজেশন
            // এটি বড় কোয়েরিতে ডিলিট হওয়া ডেটা বাদ দিতে সাহায্য করবে
            $table->index(['deleted_at', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_categories');
    }
};