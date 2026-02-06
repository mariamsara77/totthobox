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
        Schema::create('search_index_for_users', function (Blueprint $table) {
             $table->id();
            $table->string('searchable_type');
            $table->unsignedBigInteger('searchable_id');
            $table->text('content');
            $table->timestamps();

            $table->index(['searchable_type', 'searchable_id']);
            $table->fullText('content');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('search_index_for_users');
    }
};
