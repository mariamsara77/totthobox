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
        Schema::create('qurans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sura_id')->constrained()->onDelete('cascade');
            $table->foreignId('para_id')->constrained()->onDelete('cascade');
            $table->integer('ayat_no'); // সূরার ভেতরে কত নম্বর আয়াত

            $table->longText('text_arabic');
            $table->longText('text_bangla');
            $table->longText('text_english')->nullable();

            $table->string('audio_url')->nullable();
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // --- ADVANCED INDEXING (Maximum Efficiency for Quran Apps) ---

            // ১. নির্দিষ্ট সূরার আয়াতগুলো ক্রমানুসারে দ্রুত লোড করার জন্য (সবচেয়ে বেশি ব্যবহৃত হবে)
            // এটি SELECT * FROM qurans WHERE sura_id = ? AND is_active = 1 ORDER BY ayat_no ASC কোডকে সুপার ফাস্ট করবে
            $table->index(['sura_id', 'is_active', 'ayat_no'], 'idx_sura_ayat_flow');

            // ২. পারা অনুযায়ী আয়াত পড়ার সময় পারফরম্যান্স ঠিক রাখতে
            // এটি WHERE para_id = ? AND is_active = 1 ORDER BY sura_id, ayat_no কোডকে অপ্টিমাইজ করবে
            $table->index(['para_id', 'is_active', 'sura_id', 'ayat_no'], 'idx_para_ayat_flow');

            // ৩. রিলেশনাল লুকআপের জন্য কম্পোজিট ইনডেক্স
            $table->index(['sura_id', 'para_id'], 'idx_sura_para_lookup');

            // ৪. স্ট্যাটাস ফিল্টারিং
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();


        Schema::dropIfExists('qurans');

        Schema::enableForeignKeyConstraints();
    }
};