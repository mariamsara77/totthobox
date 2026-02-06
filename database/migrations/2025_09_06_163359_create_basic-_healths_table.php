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
        Schema::create('basic_healths', function (Blueprint $table) {
            $table->id();
            $table->string('title');                   // Content title
            $table->longText('description');           // Detailed content
            $table->string('type')->nullable();        // Nutrition, Exercise, etc
            $table->text('summary')->nullable();       // Short description
            $table->string('source')->nullable();      // Reference link/source
            $table->string('author')->nullable();      // Contributor
            $table->string('tags')->nullable();        // Comma separated tags or JSON
            $table->string('slug')->unique();          // Unique identifier
            $table->tinyInteger('status')->default(0); // 0 = Draft, 1 = Published
            $table->string('image')->nullable();       // Image URL

            // SEO fields
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();

            // Audit fields
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('view_count')->default(0);
            $table->boolean('is_featured')->default(false);

            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('basic_healths');
    }
};
