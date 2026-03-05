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

            // amount = amount of that nutrient per serving
            $table->decimal('amount', 8, 2)->default(0);
            $table->string('note')->nullable();

            // minimal audit for pivot
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // --- ADVANCED INDEXING (Pivot Table Optimization) ---

            // ১. একটি খাবারের সব নিউট্রিয়েন্ট দ্রুত লোড করার জন্য
            // WHERE food_id = ?
            $table->index(['food_id', 'amount'], 'idx_food_nutrient_lookup');

            // ২. ডুপ্লিকেট রোধ এবং রিভার্স সার্চ (একটি নিউট্রিয়েন্ট কোন খাবারে কতটুকু আছে)
            // এটি WHERE nutrient_id = ? ORDER BY amount DESC কোয়েরিকে সুপার ফাস্ট করবে
            // এটি লিডারবোর্ড বা টপ ফুড লিস্টের জন্য খুব কার্যকর
            $table->index(['nutrient_id', 'amount'], 'idx_nutrient_food_ranking');

            // ৩. সফট ডিলিট অপ্টিমাইজেশন
            $table->index('deleted_at');

            // ৪. একই খাবারে একই নিউট্রিয়েন্ট দুইবার এন্ট্রি হওয়া রোধ করতে (Data Integrity)
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