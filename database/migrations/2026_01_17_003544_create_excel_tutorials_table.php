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
        Schema::create('excel_tutorials', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // লেসনের নাম (উদাঃ VLOOKUP এর ব্যবহার)
            $table->string('slug')->unique(); // URL এর জন্য

            // W3Schools স্টাইল চ্যাপ্টার ম্যানেজমেন্ট
            $table->string('chapter_name'); // উদাঃ Excel Formulas, Excel Data Analysis
            $table->integer('position')->default(0); // সিরিয়াল নম্বর (১, ২, ৩...)

            $table->longText('description')->nullable(); // লেসনের বিস্তারিত আলোচনা (বাংলায়)
            $table->text('excel_formula')->nullable(); // হাইলাইটেড ফর্মুলা বক্সের জন্য

            // SEO এবং মেটা ডাটা
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            $table->boolean('is_published')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('excel_tutorials');
    }
};