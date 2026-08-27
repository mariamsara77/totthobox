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
        Schema::create('live_matches', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('stream_url')->nullable();
            $table->boolean('is_live')->default(false)->index(); // কুয়েরি অপ্টিমাইজেশনের জন্য ইনডেক্স
            $table->softDeletes(); // সফট ডিলিট হ্যান্ডেল করার জন্য
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_matches');
    }
};
