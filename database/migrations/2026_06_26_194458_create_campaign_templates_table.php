<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('campaign_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->unsignedBigInteger('template_id');
            $table->timestamps();

            $table->unique(['campaign_id', 'template_id']);
            $table->foreign('campaign_id')->references('id')->on('whatsapp_campaigns')->onDelete('cascade');
            $table->foreign('template_id')->references('id')->on('whatsapp_templates')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_templates');
    }
};