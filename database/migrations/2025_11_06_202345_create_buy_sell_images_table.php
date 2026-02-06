<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buy_sell_images', function (Blueprint $table) {
            $table->id();

            $table->foreignId('buy_sell_post_id')
    ->constrained('buy_sell_posts')
    ->cascadeOnDelete();

            $table->string('path');
            $table->string('disk')->default('public');
            $table->boolean('is_primary')->default(false)->index();
            $table->string('alt_text')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buy_sell_images');
    }
};
