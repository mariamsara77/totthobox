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
        Schema::create('paras', function (Blueprint $table) {
            $table->id();
            $table->integer('para_number')->unique();
            $table->string('name_arabic');
            $table->string('name_english');
            $table->string('name_bangla');
            $table->string('slug')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            // Indexing
            $table->index(['para_number', 'is_active'], 'idx_para_num_active');
            $table->index(['is_active', 'para_number'], 'idx_para_listing');
            $table->index('name_bangla');
            $table->index('name_english');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ফরেন কি চেক সাময়িকভাবে বন্ধ করা
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('paras');

        // আবার চালু করা
        Schema::enableForeignKeyConstraints();
    }
};