<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('whatsapp_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number')->unique()->comment('International format: +88XXXX');
            $table->enum('status', ['pending', 'processing', 'sent', 'failed', 'skipped', 'invalid'])->default('pending');
            $table->integer('campaign_id')->nullable()->comment('Campaign foreign key');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('last_retry_at')->nullable();
            $table->string('created_by')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('campaign_id');
            $table->index('phone_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_numbers');
    }
};