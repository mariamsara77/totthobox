<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(Str::uuid());
            $table->text('content');

            // Polymorphic relationship
            $table->uuidMorphs('commentable'); // Creates commentable_id (UUID) and commentable_type

            // User who commented
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // For replies/nested comments
            $table->uuid('parent_id')->nullable();
            $table->integer('depth')->default(0)->index();

            // Soft deletes for moderation
            $table->softDeletes();
            $table->timestamps();

            // Performance indexes
            $table->index(['commentable_type', 'commentable_id', 'created_at']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
