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
        Schema::create('signs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sign_category_id');
            $table->string('name')->nullable();
            $table->string('slug')->nullable()->unique();
            $table->text('description')->nullable();
            $table->text('details')->nullable();
            $table->text('others')->nullable();
            $table->tinyInteger('status')->default(1)->comment('0 = inactive, 1 = active');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('sign_category_id')
                ->references('id')
                ->on('sign_categories')
                ->onDelete('cascade');
            $table->index(['sign_category_id', 'status'], 'idx_sign_cat_status');
            $table->index('name', 'idx_sign_name');
            $table->index(['deleted_at', 'status'], 'idx_sign_deleted_status');
            $table->boolean('is_featured')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signs');
    }
};
