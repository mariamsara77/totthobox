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

            // Relationship with category
            $table->unsignedBigInteger('sign_category_id');

            // Core fields
            $table->string('image')->nullable()->comment('ছবি');
            $table->string('name_bn')->comment('চিহ্নের নাম (বাংলা)');
            $table->string('name_en')->nullable()->comment('Sign Name (English)');
            $table->text('description_bn')->nullable()->comment('ব্যাখ্যা (বাংলা)');
            $table->text('description_en')->nullable()->comment('Description (English)');
            $table->text('details')->nullable();
            $table->text('others')->nullable();

            // Audit / lifecycle
            $table->tinyInteger('status')->default(1)->comment('0 = inactive, 1 = active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('sign_category_id')
                ->references('id')
                ->on('sign_categories')
                ->onDelete('cascade');

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
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
