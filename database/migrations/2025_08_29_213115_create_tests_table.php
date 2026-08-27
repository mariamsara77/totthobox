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
        Schema::create('tests', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_level_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('total_questions');
            $table->unsignedInteger('total_marks');
            $table->unsignedInteger('duration');
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->boolean('is_published')->default(false);
            $table->string('slug')->unique();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('published_by')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('published_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['class_level_id', 'subject_id', 'is_published'], 'idx_test_lookup');
            $table->index(['start_time', 'end_time', 'is_published'], 'idx_test_schedule');
            $table->index(['status', 'is_featured'], 'idx_test_featured_stats');
            $table->index(['is_published', 'published_at']);
            $table->index('title');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tests');
    }
};