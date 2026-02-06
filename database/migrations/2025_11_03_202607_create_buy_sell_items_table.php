<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('buy_sell_items', function (Blueprint $table) {
            $table->id();

            // Core Data
            $table->string('title');
            $table->string('slug')->unique()->index();
            $table->string('thumbnail')->nullable();
            $table->longText('description')->nullable();
            $table->text('note')->nullable();

            // Relational Mapping
            $table->foreignId('buy_sell_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Publication & Status Control
            $table->enum('status', ['draft', 'pending', 'published', 'archived'])
                  ->default('draft')
                  ->index();
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamp('published_at')->nullable()->index();

            // Pricing & Analytics
            $table->decimal('price', 12, 2)->nullable()->index();
            $table->unsignedBigInteger('view_count')->default(0)->index();

            // SEO Metadata
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();

            // Audit Trail
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();

            // Tech Metadata
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            // Time Tracking
            $table->timestamps();
            $table->softDeletes();

            // Compound Indexes for performance
            $table->index(['status', 'is_active', 'published_at']);
            $table->index(['buy_sell_category_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buy_sell_items');
    }
};
