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
        Schema::create('excel_tutorials', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // লেসনের নাম
            $table->string('slug')->unique(); // URL এর জন্য

            // W3Schools স্টাইল চ্যাপ্টার ম্যানেজমেন্ট
            $table->string('chapter_name'); // উদাঃ Excel Formulas
            $table->integer('position')->default(0); // সিরিয়াল নম্বর

            $table->longText('description')->nullable();
            $table->text('excel_formula')->nullable();

            // SEO এবং মেটা ডাটা
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            $table->boolean('is_published')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // --- ADVANCED INDEXING (Documentation & LMS Optimization) ---

            // ১. চ্যাপ্টার অনুযায়ী লেসনগুলো ক্রমানুসারে দ্রুত লোড করার জন্য (সবচেয়ে গুরুত্বপূর্ণ)
            // এটি WHERE chapter_name = ? AND is_published = 1 ORDER BY position ASC কোয়েরিকে সুপার ফাস্ট করবে।
            $table->index(['chapter_name', 'is_published', 'position'], 'idx_chapter_lesson_flow');

            // ২. ড্যাশবোর্ড বা লিস্ট ভিউ এর জন্য ফিচারড/পাবলিশড লেসন ফিল্টার
            $table->index(['is_published', 'created_at'], 'idx_published_latest');

            // ৩. টাইটেল ভিত্তিক সার্চ (যদি ইউজার কোনো নির্দিষ্ট ফর্মুলা সার্চ করে)
            $table->index('title');

            // ৪. সফট ডিলিট অপ্টিমাইজেশন
            $table->index('deleted_at');
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