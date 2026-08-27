<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('news_headings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('source_link');
            $table->string('source_name');
            $table->string('source_key');           // e.g. 'prothom_alo', 'daily_star'
            $table->string('category')->nullable(); // e.g. 'national', 'sports', 'business'
            $table->longText('image_url')->nullable();
            $table->string('language', 10)->default('bn'); // 'bn' | 'en'
            $table->timestamp('published_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['source_key', 'published_at']);
            $table->index(['language', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_headings');
    }
};