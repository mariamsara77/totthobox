<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            // Social Provider IDs
            $table->string('github_id')->nullable()->unique()->after('google_id');
            $table->string('facebook_id')->nullable()->unique()->after('github_id');
            $table->string('twitter_id')->nullable()->unique()->after('facebook_id');

            // Email কে nullable করা হচ্ছে (Twitter/Facebook এর জন্য দরকার)
            $table->string('email')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['github_id', 'facebook_id', 'twitter_id']);

            // চাইলে email আবার unique + not null করে দিতে পারো
            // $table->string('email')->nullable(false)->unique()->change();
        });
    }
};