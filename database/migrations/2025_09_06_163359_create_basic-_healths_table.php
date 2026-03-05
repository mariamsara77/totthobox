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
        Schema::create('basic_healths', function (Blueprint $table) {
            $table->id();
            $table->string('title');                   // Content title
            $table->longText('description');           // Detailed content
            $table->string('type')->nullable();        // Nutrition, Exercise, etc
            $table->text('summary')->nullable();       // Short description
            $table->string('source')->nullable();      // Reference link/source
            $table->string('author')->nullable();      // Contributor
            $table->string('tags')->nullable();        // Comma separated tags
            $table->string('slug')->unique();          // Unique identifier
            $table->tinyInteger('status')->default(0); // 0 = Draft, 1 = Published
            $table->string('image')->nullable();       // Image URL

            // SEO fields
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();

            // Audit fields
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

            // --- ADVANCED INDEXING (Maximum Search Efficiency) ---

            // ১. টাইপ এবং স্ট্যাটাস অনুযায়ী ফিল্টারিং (যেমন: পুষ্টি বিষয়ক পাবলিশড পোস্টগুলো দেখাও)
            // WHERE type = 'Nutrition' AND status = 1
            $table->index(['type', 'status'], 'idx_health_type_status');

            // ২. জনপ্রিয় পোস্ট এবং ফিচারড সর্টিং 
            // ORDER BY is_featured DESC, view_count DESC
            $table->index(['status', 'is_featured', 'view_count'], 'idx_health_popular');

            // ৩. পাবলিশ ডেট অনুযায়ী সর্টিং (সর্বশেষ আপডেট বা আর্টিকেলের জন্য)
            $table->index(['status', 'published_at'], 'idx_health_latest');

            // ৪. স্লাগ এবং টাইটেল দিয়ে দ্রুত লুকআপের জন্য
            // স্লাগ অলরেডি ইউনিক, তাই টাইটেলকে আলাদা ইনডেক্স করা হলো
            $table->index('title');

            // ৫. সফট ডিলিট অপ্টিমাইজেশন (প্রতিটি কোয়েরিতে এটি চেক হয়)
            $table->index('deleted_at');

            // ৬. ইউজার এ্যাক্টিভিটি বা অডিটিং এর জন্য
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('basic_healths');
    }
};