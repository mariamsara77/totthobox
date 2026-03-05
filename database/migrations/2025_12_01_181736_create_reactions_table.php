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
        Schema::create('reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // polymorphic (using uuidMorphs as requested)
            $table->uuidMorphs('reactable');

            // Reaction type: like, love, haha, etc.
            $table->string('type', 20); // Length limited for better indexing

            $table->timestamps();

            // --- ADVANCED INDEXING (Social Engagement Performance) ---

            // ১. ডুপ্লিকেট রোধ এবং ডেটা ইন্টিগ্রিটি (হুবহু আগের লজিক, কিন্তু ইনডেক্স নাম সহ)
            $table->unique(['user_id', 'reactable_id', 'reactable_type', 'type'], 'unique_user_reaction_type');

            // ২. কন্টেন্ট ভিত্তিক রিয়েকশন কাউন্ট (যেমন: একটি পোস্টে মোট কয়টি 'Love' রিয়েকশন আছে)
            // এটি SELECT count(*) WHERE reactable_id = ? AND type = 'love' কোয়েরিকে পানির মতো ফাস্ট করবে।
            $table->index(['reactable_id', 'reactable_type', 'type'], 'idx_content_reaction_count');

            // ৩. ইউজারের নিজের রিয়েকশন চেক (ইউজার যখন স্ক্রল করবে তখন সে কোন পোস্টে কী রিয়েকশন দিয়েছে তা দেখাতে)
            // এটি WHERE user_id = ? AND reactable_type = ? কোয়েরিকে অপ্টিমাইজ করবে।
            $table->index(['user_id', 'reactable_type'], 'idx_user_activity_lookup');

            // ৪. নির্দিষ্ট সময়ের ট্রেন্ডিং কন্টেন্ট বের করার জন্য
            $table->index(['created_at', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reactions');
    }
};