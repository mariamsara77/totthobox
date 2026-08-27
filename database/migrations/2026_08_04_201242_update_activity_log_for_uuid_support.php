<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            // subject_id এবং causer_id কে UUID সাপোর্ট করার জন্য পরিবর্তন
            $table->uuid('subject_id')->nullable()->change();
            $table->uuid('causer_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            // আগের অবস্থায় ফিরিয়ে আনতে চাইলে (সাধারণত bigint)
            $table->unsignedBigInteger('subject_id')->nullable()->change();
            $table->unsignedBigInteger('causer_id')->nullable()->change();
        });
    }
};
