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
        Schema::create('test_questions', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->foreignId('test_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();

            // Order field (পরীক্ষায় প্রশ্নের ক্রম সাজানোর জন্য)
            $table->unsignedInteger('order')->default(0);

            $table->timestamps();

            // --- ADVANCED INDEXING & CONSTRAINTS ---

            // ১. ডুপ্লিকেট এন্ট্রি রোধ এবং দ্রুত লুকআপের জন্য ইউনিক কম্পোজিট ইনডেক্স
            // এটি নিশ্চিত করবে এক পরীক্ষায় এক প্রশ্ন দুইবার আসবে না
            $table->unique(['test_id', 'question_id'], 'idx_test_ques_unique');

            // ২. সর্টিং অপ্টিমাইজেশন (পরীক্ষায় প্রশ্নগুলো সিরিয়াল অনুযায়ী দেখানোর জন্য)
            // WHERE test_id = ? ORDER BY order ASC কোয়েরিকে সুপার ফাস্ট করবে
            $table->index(['test_id', 'order'], 'idx_test_questions_ordering');

            // ৩. রিভার্স লুকআপ ইনডেক্স
            // একটি নির্দিষ্ট প্রশ্ন কোন কোন পরীক্ষায় আছে তা দ্রুত বের করার জন্য
            $table->index('question_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_questions');
    }
};