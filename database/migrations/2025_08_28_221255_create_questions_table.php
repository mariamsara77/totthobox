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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained();
            $table->foreignId('class_level_id')->constrained();
            $table->text('question_text');
            $table->text('option_a');
            $table->text('option_b');
            $table->text('option_c');
            $table->text('option_d');
            $table->enum('correct_answer', ['a', 'b', 'c', 'd']);
            $table->integer('marks')->default(1);
            $table->integer('difficulty_level')->default(1);
            $table->text('explanation')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['class_level_id', 'subject_id', 'is_active'], 'idx_ques_class_sub_active');
            $table->index(['difficulty_level', 'is_active'], 'idx_ques_difficulty');
            $table->index(['is_active', 'is_featured'], 'idx_ques_stats');
            $table->index(['deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};