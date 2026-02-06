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
        Schema::create('reactions', function (Blueprint $table) {
             $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');

    // polymorphic
    $table->uuidMorphs('reactable');

    // Any reaction: like, dislike, love, haha, wow...
    $table->string('type');

    $table->timestamps();

    // Prevent duplicate reactions of same type by same user
    $table->unique(['user_id', 'reactable_id', 'reactable_type', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reactions');
    }
};
