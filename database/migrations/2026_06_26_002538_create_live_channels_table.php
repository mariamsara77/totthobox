<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('live_channels')) {
            return; // টেবিল থাকলে মাইগ্রেশন এখানে থেমে যাবে
        }
        Schema::create('live_channels', function (Blueprint $table) {
            $table->id();

            // ── Identity ────────────────────────────────────────────────
            $table->string('title');
            $table->string('slug')->unique()->nullable();
            $table->string('stream_url');
            $table->text('embed_url')->nullable();     // iframe fallback

            // ── Classification ──────────────────────────────────────────
            $table->enum('category', [
                'worldcup',     // FIFA World Cup 2026
                'bangladesh',   // Bangladesh TV channels
                'football',     // Global football/sports
            ])->default('football')->index();

            $table->string('country_code', 5)->nullable()->index();   // BD, US, GB …
            $table->string('language', 50)->nullable();               // bengali, english …
            $table->string('broadcaster')->nullable();                 // beIN, T Sports …
            $table->string('logo_url')->nullable();

            // ── Status ──────────────────────────────────────────────────
            $table->boolean('is_live')->default(false)->index();
            $table->boolean('is_featured')->default(false)->index();   // pinned at top
            $table->integer('sort_order')->default(0)->index();

            // ── Health tracking ─────────────────────────────────────────
            $table->timestamp('last_checked_at')->nullable();
            $table->timestamp('last_live_at')->nullable();
            $table->integer('consecutive_failures')->default(0);
            $table->tinyInteger('health_score')->default(0);           // 0–100

            // ── Source metadata ─────────────────────────────────────────
            $table->string('source_label')->nullable();                // "IPTV-Org Bangladesh"
            $table->json('m3u_meta')->nullable();                      // raw parsed meta

            $table->timestamps();
            $table->softDeletes();

            // Composite indexes for common query patterns
            $table->index(['category', 'is_live', 'sort_order']);
            $table->index(['is_live', 'is_featured', 'sort_order']);
            $table->unique(['stream_url']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_channels');
    }
};