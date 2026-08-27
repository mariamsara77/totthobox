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
        Schema::create('app_resources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('version')->nullable();
            $table->string('platform');
            $table->text('description')->nullable();

            $table->string('download_type')->default('external');
            $table->text('external_url')->nullable();
            $table->string('download_password')->nullable();
            $table->string('masked_extension')->nullable();

            $table->unsignedBigInteger('download_count')->default(0);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_resources');
    }
};
