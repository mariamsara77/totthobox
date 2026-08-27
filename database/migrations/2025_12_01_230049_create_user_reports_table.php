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
        Schema::create('user_reports', function (Blueprint $table) {
            $table->id();

            // কে রিপোর্ট করলো
            $table->unsignedBigInteger('reported_by');

            // কি জিনিসকে রিপোর্ট করা হলো (Global Polymorphic)
            $table->string('target_type');   // User / Post / Comment / Message
            $table->unsignedBigInteger('target_id');

            // Reason fields
            $table->string('reason')->nullable();
            $table->text('details')->nullable();

            // Admin workflow
            $table->string('status')->default('pending'); // pending / reviewed / resolved

            $table->timestamps();

            // --- PERFORMANCE INDEXING (Admin Optimization) ---

            // ১. এডমিন প্যানেলের জন্য (পেন্ডিং রিপোর্টগুলো দ্রুত দেখার জন্য)
            // এটি WHERE status = 'pending' ORDER BY created_at DESC কোয়েরিকে অপ্টিমাইজ করবে
            $table->index(['status', 'created_at'], 'idx_reports_admin_workflow');

            // ২. পলিমরফিক লুকআপ (একটি নির্দিষ্ট পোস্ট বা ইউজারের সব রিপোর্ট দেখার জন্য)
            // এটি WHERE target_type = ? AND target_id = ? কোয়েরিকে সুপার ফাস্ট করবে
            $table->index(['target_type', 'target_id'], 'idx_reports_polymorphic_target');

            // ৩. একই ইউজার বারবার রিপোর্ট করছে কি না তা চেক করার জন্য
            $table->index(['reported_by', 'status']);

            // ৪. ফরেন কি কনস্ট্রেইন্ট (ডেটা ইন্টিগ্রিটির জন্য)
            $table->foreign('reported_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_reports');
    }
};