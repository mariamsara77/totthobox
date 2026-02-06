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
        Schema::create('sign_categories', function (Blueprint $table) {
            $table->id();

            // Core content fields
            $table->string('name'); // mandatory
            $table->string('title')->nullable();
            $table->string('short_title')->nullable();
            $table->text('short_description')->nullable();
            $table->text('long_description')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('slug')->unique();
            $table->string('icon')->nullable();

            // Ownership / status
            $table->unsignedBigInteger('user_id')->nullable();
            $table->tinyInteger('status')->default(0)->index(); // indexed for filtering

            // SEO fields
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();

            // Audit fields
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('published_by')->nullable();

            // Engagement & promotion
            $table->integer('view_count')->default(0);
            $table->boolean('is_featured')->default(false)->index();

            // Logging
            $table->string('ip_address', 45)->nullable(); // IPv4 + IPv6 support
            $table->text('user_agent')->nullable();

            // Standard Laravel timestamps
            $table->timestamps();
            $table->softDeletes();

            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('published_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sign_categories');
    }
};
