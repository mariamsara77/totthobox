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
        Schema::create('excel_tutorials', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('chapter_name');
            $table->integer('position')->default(0);
            $table->longText('description')->nullable();
            $table->text('excel_formula')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['chapter_name', 'is_published', 'position'], 'idx_chapter_lesson_flow');
            $table->index(['is_published', 'created_at'], 'idx_published_latest');
            $table->index('title');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('excel_tutorials');
    }
};