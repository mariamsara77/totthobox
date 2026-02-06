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
        Schema::create('suras', function (Blueprint $table) {
            $table->id();
            $table->integer('sura_no')->unique(); // সূরা নং (১-১১৪)
            $table->string('name_arabic');
            $table->string('name_english');
            $table->string('name_bangla');
            $table->string('meaning_bangla')->nullable();
            $table->enum('revelation_type', ['Meccan', 'Medinan'])->index();
            $table->integer('total_ayat');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamps();

            // Performance Indexing
            $table->index(['sura_no', 'is_active']);   
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suras');
    }
};