<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // 1. Visitors Table
        Schema::create('visitors', function (Blueprint $table) {
    $table->id();
    $table->string('hash', 64)->unique();
    $table->string('ip_address', 45)->nullable()->index();
    $table->text('user_agent')->nullable(); // এই লাইনটি যোগ করুন
    $table->string('browser', 50)->nullable();
    $table->string('os', 50)->nullable();
    $table->string('device', 50)->nullable();
    $table->string('country', 10)->nullable();
    $table->string('city', 100)->nullable();
    $table->string('latitude')->nullable();
    $table->string('longitude')->nullable();
    $table->string('timezone', 50)->nullable();
    $table->text('referrer')->nullable();
    $table->string('referrer_domain')->nullable();
    $table->boolean('is_bot')->default(false);
    $table->timestamp('first_seen_at')->nullable();
    $table->timestamp('last_seen_at')->nullable();
    $table->string('screen_resolution', 25)->nullable();
    $table->float('ram_gb')->nullable()->index();
    $table->integer('cpu_cores')->nullable();
    $table->string('network_type', 20)->nullable();
    $table->timestamps();

    $table->index(['country', 'is_bot'], 'idx_country_bot');
    $table->index(['last_seen_at', 'is_bot'], 'idx_active_users');
});

        // 2. Visitor Sessions Table
        Schema::create('visitor_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('visitor_id')->constrained()->cascadeOnDelete();
            $table->string('session_hash', 64)->unique();
            $table->timestamp('started_at')->index();
            $table->timestamp('ended_at')->nullable()->index();
            $table->integer('duration')->unsigned()->nullable()->index(); // লং সেশন ফিল্টার করতে
            $table->timestamps();

            // Composite Index: সেশনের সময়কাল এবং ইউজারের সম্পর্ক দ্রুত করতে
            $table->index(['visitor_id', 'started_at'], 'idx_visitor_session_time');
        });

        // 3. Page Views Table
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->constrained()->cascadeOnDelete();
            $table->uuid('session_id')->nullable();
            $table->string('route_name')->nullable()->index();
            $table->integer('status_code')->index(); // 404 বা 500 এরর ট্র্যাকিং ফাস্ট হবে
            $table->text('url');
            $table->string('method', 10)->default('GET');
            $table->json('query_params')->nullable();
            $table->decimal('load_time', 10, 3)->nullable()->index(); // স্লো পেজ সহজে ধরা যাবে
            $table->boolean('is_ajax')->default(false);
            $table->boolean('is_secure')->default(false);
            $table->timestamps();

            // Foreign Key & Composite Index
            $table->foreign('session_id')->references('id')->on('visitor_sessions')->nullOnDelete();
            $table->index(['session_id', 'created_at'], 'idx_session_page_order');
            $table->index(['created_at', 'route_name'], 'idx_traffic_flow');
        });

        // 4. Visitor Events Table
        Schema::create('visitor_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('visitor_id')->constrained()->cascadeOnDelete();
            $table->uuid('session_id')->nullable();
            $table->string('event_type')->index();
            $table->string('event_name')->index();
            $table->json('event_data')->nullable();
            $table->timestamps();

            $table->foreign('session_id')->references('id')->on('visitor_sessions')->nullOnDelete();
            $table->index(['visitor_id', 'event_type', 'created_at'], 'idx_user_behavior');
        });
    }

    public function down()
    {
        Schema::dropIfExists('visitor_events');
        Schema::dropIfExists('page_views');
        Schema::dropIfExists('visitor_sessions');
        Schema::dropIfExists('visitors');
    }
};