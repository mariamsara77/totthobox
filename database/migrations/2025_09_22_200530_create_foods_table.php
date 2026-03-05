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
        Schema::create('foods', function (Blueprint $table) {
            $table->id();
            $table->string('name_bn');
            $table->string('name_en')->nullable();
            $table->string('slug')->unique();
            $table->text('description')->nullable();

            // macronutrients / basics
            $table->unsignedInteger('calorie')->nullable();      // kcal
            $table->decimal('carb', 8, 2)->nullable();           // grams
            $table->decimal('protein', 8, 2)->nullable();        // grams
            $table->decimal('fat', 8, 2)->nullable();            // grams
            $table->decimal('fiber', 8, 2)->nullable();          // grams
            $table->string('serving_size')->nullable();          // "100g", "1 cup"

            // Category relation
            $table->foreignId('food_category_id')->nullable()->constrained()->nullOnDelete();

            // SEO / status / image
            $table->tinyInteger('status')->default(0);
            $table->string('image')->nullable();
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

            // --- ADVANCED INDEXING (Performance Boost) ---

            // ১. ক্যাটাগরি অনুযায়ী একটিভ খাবারগুলো দ্রুত পাওয়ার জন্য
            // WHERE food_category_id = ? AND status = 1
            $table->index(['food_category_id', 'status'], 'idx_food_category_status');

            // ২. ক্যালরি এবং পুষ্টিগুণ ভিত্তিক ফিল্টারিং (যেমন: হাই প্রোটিন বা লো ক্যালরি ফুড)
            // এটি WHERE calorie <= ? AND status = 1 কোয়েরিকে সুপার ফাস্ট করবে
            $table->index(['status', 'calorie'], 'idx_food_calorie_filter');
            $table->index(['status', 'protein'], 'idx_food_protein_filter');

            // ৩. নাম ভিত্তিক সার্চ (বাংলা এবং ইংরেজি)
            $table->index('name_bn');
            $table->index('name_en');

            // ৪. ফিচারড এবং মোস্ট ভিউড সর্টিং (জনপ্রিয় খাবারের জন্য)
            $table->index(['is_featured', 'view_count', 'status'], 'idx_food_popular_featured');

            // ৫. সফট ডিলিট এবং টাইমস্ট্যাম্প অপ্টিমাইজেশন
            $table->index('deleted_at');
            $table->index('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('foods');
    }
};