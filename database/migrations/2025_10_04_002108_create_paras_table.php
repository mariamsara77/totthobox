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
        Schema::create('paras', function (Blueprint $table) {
            $table->id();
            $table->integer('para_number')->unique(); // পারা নং (১-৩০)
            $table->string('name_arabic');
            $table->string('name_english');
            $table->string('name_bangla');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            // Index for faster ordering
            $table->index(['para_number', 'is_active']); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paras');
    }
};