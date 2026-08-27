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
        Schema::create('indexed_urls', function (Blueprint $table) {
            $table->id();
            // 255 char utf8mb4 unique index MySQL/PostgreSQL/SQLite সবখানেই নিরাপদ (key length limit-এর মধ্যে)
            $table->string('url')->unique();
            $table->date('last_crawled')->nullable();
            $table->boolean('is_indexed')->default(false);
            $table->string('status')->default('pending')->index(); // pending, queued, success, failed, quota_exceeded
            $table->string('source')->default('manual'); // manual, csv, sitemap
            $table->timestamp('last_pushed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indexed_urls');
    }
};
