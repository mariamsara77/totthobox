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
        Schema::create('suras', function (Blueprint $table) {
            $table->id();
            $table->integer('sura_no')->unique(); // সূরা নং (১-১১৪)
            $table->string('name_arabic');
            $table->string('name_english');
            $table->string('name_bangla');
            $table->string('meaning_bangla')->nullable();
            $table->enum('revelation_type', ['Meccan', 'Medinan']);
            $table->integer('total_ayat');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->softDeletes();

            // --- ADVANCED INDEXING (Optimization for Quran App) ---

            // ১. সূরার সিরিয়াল এবং স্ট্যাটাস অনুযায়ী দ্রুত লিস্টিং (হোমপেজ বা সূচিপত্র)
            // এটি ORDER BY sura_no ASC কোয়েরিকে অপ্টিমাইজ করবে
            $table->index(['is_active', 'sura_no'], 'idx_sura_list_active');

            // ২. মক্কী বা মাদানী সূরা আলাদাভাবে ফিল্টার করার জন্য
            // WHERE revelation_type = 'Meccan' AND is_active = 1
            $table->index(['revelation_type', 'is_active'], 'idx_sura_revelation');

            // ৩. ফিচারড এবং পপুলার সূরাগুলো আগে দেখানোর জন্য
            $table->index(['is_featured', 'is_active'], 'idx_sura_featured');

            // ৪. নামের ওপর সার্চ পারফরম্যান্স (বাংলা, ইংরেজি ও আরবি)
            $table->index('name_bangla');
            $table->index('name_english');
            $table->index('name_arabic');

            // ৫. আয়াত সংখ্যা অনুযায়ী সর্টিং (যদি ইউজার বড়/ছোট সূরা আগে দেখতে চায়)
            $table->index(['total_ayat', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('suras');

        Schema::enableForeignKeyConstraints();
    }
};