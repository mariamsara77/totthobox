<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();

            $table->string('title')->nullable();
            $table->string('type')->default('private'); // private | group | system
            $table->timestamp('last_message_at')->nullable()->index();

            $table->timestamps();
            $table->softDeletes();

            // Performance optimization for large chat datasets
            $table->index(['type']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
