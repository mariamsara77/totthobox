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
        Schema::create('basic_islams', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();  // শিরোনাম
            $table->text('description')->nullable();  // বর্ণনা
            $table->string('image')->nullable();  // ঐতিহাসিক ছবি বা ব্যানার
            $table->string('type')->nullable(); // ধরন (যেমন: নামাজ, রোজা, ইতিহাস)
            $table->string('tags')->nullable();
            $table->string('slug')->unique();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->tinyInteger('status')->default(0);

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

            // --- Foreign key constraints ---
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('published_by')->references('id')->on('users')->onDelete('set null');

            // --- ADVANCED INDEXING (Performance Boost) ---

            // ১. টাইপ বা ক্যাটাগরি ভিত্তিক দ্রুত সার্চের জন্য
            // WHERE type = 'Namaj' AND status = 1
            $table->index(['type', 'status'], 'idx_islam_type_status');

            // ২. পপুলারিটি এবং ফিচারড সর্টিং (সবচেয়ে বেশি পড়া বা বিশেষ পোস্ট)
            // ORDER BY is_featured DESC, view_count DESC
            $table->index(['status', 'is_featured', 'view_count'], 'idx_islam_featured_popular');

            // ৩. পাবলিশ ডেট অনুযায়ী সর্টিং (সর্বশেষ আপডেট বা পোস্ট)
            $table->index(['status', 'published_at']);

            // ৪. টাইটেল দিয়ে সার্চ অপ্টিমাইজেশন (যদি স্লাগ ছাড়া টাইটেল দিয়েও সার্চ করা হয়)
            $table->index('title');

            // ৫. ইউজার ট্র্যাকিং এবং অডিট ট্রেইল দ্রুত করার জন্য
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('basic_islams');
    }
};