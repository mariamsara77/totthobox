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
        Schema::create('qurans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sura_id')->constrained()->onDelete('cascade');
            $table->foreignId('para_id')->constrained()->onDelete('cascade');
            $table->integer('ayat_no'); // সূরার ভেতরে কত নম্বর আয়াত

            $table->longText('text_arabic');
            $table->longText('text_bangla');
            $table->longText('text_english')->nullable();

            $table->string('audio_url')->nullable();
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            // Critical Performance Indexes
            $table->index(['sura_id', 'ayat_no']); // সূরা ভিত্তিক আয়াত খুঁজতে
            $table->index(['para_id', 'ayat_no']); // পারা ভিত্তিক আয়াত খুঁজতে
            $table->index(['sura_id', 'para_id']); // কম্পোজিট ইনডেক্স   
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qurans');
    }
};