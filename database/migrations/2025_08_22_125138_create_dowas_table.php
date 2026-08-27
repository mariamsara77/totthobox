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
        Schema::create('dowas', function (Blueprint $table) {
            $table->id();
            $table->string('bangla_name')->nullable()->index();
            $table->string('arabic_name')->nullable();
            $table->longText('arabic_text')->nullable();
            $table->longText('bangla_text')->nullable();
            $table->longText('bangla_meaning')->nullable();
            $table->longText('bangla_fojilot')->nullable();
            $table->string('audio')->nullable();
            $table->longText('others')->nullable();
            $table->string('type')->nullable();
            $table->string('tags')->nullable();
            $table->string('slug')->unique();
            $table->tinyInteger('status')->default(0)->index();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->softDeletes();

            // ইনডেক্সিং
            $table->index(['type', 'status'], 'idx_dowa_type_status');
            $table->index(['status', 'is_featured'], 'idx_dowa_featured_popular');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dowas');
    }
};