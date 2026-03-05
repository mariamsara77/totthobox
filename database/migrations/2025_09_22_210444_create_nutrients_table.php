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
        Schema::create('nutrients', function (Blueprint $table) {
            $table->id();
            $table->string('name_bn');
            $table->string('name_en')->nullable();
            $table->string('slug')->unique();
            $table->string('unit', 10)->nullable(); // mg, mcg, g ইত্যাদি
            $table->text('description')->nullable();

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

            // --- ADVANCED INDEXING (Maximum Efficiency) ---

            // ১. একটিভ এবং ফিচারড নিউট্রিয়েন্ট দ্রুত পাওয়ার জন্য (যেমন: সুপারফুড বা ইম্পর্ট্যান্ট ভিটামিন)
            // এটি WHERE status = 1 AND is_featured = 1 কোয়েরিকে সুপার ফাস্ট করবে
            $table->index(['status', 'is_featured'], 'idx_nutrient_status_featured');

            // ২. নামের ওপর দ্রুত সার্চ (বাংলা ও ইংরেজি উভয় ক্ষেত্রে)
            $table->index('name_bn');
            $table->index('name_en');

            // ৩. পাবলিশ ডেট এবং ভিউ কাউন্ট সর্টিং (জনপ্রিয় নিউট্রিয়েন্টগুলোর জন্য)
            $table->index(['status', 'published_at', 'view_count'], 'idx_nutrient_popular_published');

            // ৪. সফট ডিলিট অপ্টিমাইজেশন (প্রতিটি রিড কোয়েরিতে এটি চেক হয়)
            $table->index('deleted_at');

            // ৫. অডিট এবং ইউজার অ্যাক্টিভিটি ট্র্যাকিং
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nutrients');
    }
};