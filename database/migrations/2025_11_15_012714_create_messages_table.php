<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->foreignId('sender_id')->index()->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->index()->constrained('users')->cascadeOnDelete();

            $table->text('message')->nullable();
            $table->json('meta')->nullable();

            // Boolean এবং Timestamp অপ্টিমাইজেশন
            $table->boolean('read')->default(false)->index(); // আনরিড মেসেজ দ্রুত গুনতে
            $table->timestamp('read_at')->nullable()->index();

            $table->string('attachment')->nullable();
            $table->string('attachment_type', 50)->nullable(); // টাইপ ছোট রাখা ভালো

            $table->foreignId('parent_id')->nullable()->index()->constrained('messages')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();

            // --- Composite Index (The Magic Fix) ---
            // যখন দুজন ইউজারের কথোপকথন লোড হবে, এই ইনডেক্সটি ডাটাবেজকে ১ সেকেন্ডের কাজ ০.০১ সেকেন্ডে করতে সাহায্য করবে।
            $table->index(['sender_id', 'receiver_id', 'created_at']);
            $table->index(['receiver_id', 'read']); // নোটিফিকেশন কাউন্টের জন্য
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};