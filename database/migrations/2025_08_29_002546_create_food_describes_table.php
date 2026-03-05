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
        Schema::create('food_describes', function (Blueprint $table) {
            $table->id();

            // Basic Info
            $table->string('bangla_name');
            $table->string('english_name');
            $table->string('category')->nullable();
            $table->string('sub_category')->nullable();

            // Description & Details
            $table->longText('description')->nullable();
            $table->longText('health_benefits')->nullable();
            $table->longText('nutrients')->nullable();
            $table->longText('medical_info')->nullable();
            $table->longText('combinations')->nullable();
            $table->longText('others')->nullable();
            $table->longText('Benefits')->nullable();
            $table->longText('References')->nullable();

            // Media
            $table->string('image')->nullable();

            // Meta
            $table->string('slug')->unique();

            $table->timestamps();
            $table->softDeletes();

            // --- ADVANCED INDEXING (Performance Boost) ---

            // ১. ক্যাটাগরি এবং সাব-ক্যাটাগরি ভিত্তিক দ্রুত ফিল্টারিং
            // WHERE category = 'Fruits' AND sub_category = 'Citrus'
            $table->index(['category', 'sub_category'], 'idx_food_cat_sub');

            // ২. নামের ওপর দ্রুত সার্চ (বাংলা ও ইংরেজি উভয় ক্ষেত্রে)
            $table->index('bangla_name');
            $table->index('english_name');

            // ৩. ক্যাটাগরি অনুযায়ী সর্টিং এবং লিস্টিং
            $table->index(['category', 'created_at']);

            // ৪. সফট ডিলিট অপ্টিমাইজেশন
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_describes');
    }
};