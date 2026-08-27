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
        Schema::create('establishment_bds', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->text('description')->nullable();

            // কনস্ট্রেইন্ড ব্যবহার করলে কোড ক্লিন থাকে
            $table->foreignId('division_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('thana_id')->nullable()->constrained()->nullOnDelete();

            $table->string('slug')->unique();
            $table->tinyInteger('status')->default(0)->index(); // স্ট্যাটাসে দ্রুত কুয়েরির জন্য
            $table->boolean('is_featured')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'is_featured'], 'idx_estab_popular');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('establishment_bds');
    }
};