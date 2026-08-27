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
        Schema::create('food_nutrients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('food_id')
                ->constrained('foods')
                ->cascadeOnDelete();
            $table->foreignId('nutrient_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->decimal('amount', 8, 2)->default(0);
            $table->string('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['food_id', 'amount'], 'idx_food_nutrient_lookup');
            $table->index(['nutrient_id', 'amount'], 'idx_nutrient_food_ranking');
            $table->index('deleted_at');
            $table->unique(['food_id', 'nutrient_id'], 'unique_food_nutrient');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_nutrients');
    }
};