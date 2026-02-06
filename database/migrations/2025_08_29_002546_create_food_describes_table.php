<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('food_describes', function (Blueprint $table) {
            $table->id(); // Basic Info
            $table->string('bangla_name'); // খাবারের নাম
            $table->string('english_name'); // Food name in English
            $table->string('category')->nullable(); // শাকসবজি, ফলমূল, প্রোটিন ইত্যাদি
            $table->string('sub_category')->nullable(); // পাতাবহুল শাক, সাইট্রাস, ওমেগা-৩ ইত্যাদি

            // Description & Details
            $table->longText('description')->nullable(); // সাধারণ বর্ণনা
            $table->longText('health_benefits')->nullable(); // স্বাস্থ্য উপকারিতা
            $table->longText('nutrients')->nullable(); // ভিটামিন/খনিজ উপাদান
            $table->longText('medical_info')->nullable(); // ডায়াবেটিস, হাই ব্লাড প্রেসার ইত্যাদিতে ভূমিকা
            $table->longText('combinations')->nullable(); // সুপারফুড কম্বিনেশন
            $table->longText('others')->nullable(); // অন্যান্য তথ্য
            $table->longText('Benefits')->nullable(); // অন্যান্য তথ্য
            $table->longText('References')->nullable(); // অন্যান্য তথ্য

            // Media
            $table->string('image')->nullable(); // খাবারের ছবি

            // Meta
            $table->string('slug')->unique(); // SEO-friendly URL

            $table->timestamps();
            $table->softDeletes();
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
