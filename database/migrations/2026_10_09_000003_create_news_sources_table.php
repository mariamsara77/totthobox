<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Retired migration retained so databases that already recorded its name
 * keep a matching migration file. The source catalogue is now config-driven.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Intentionally no-op. Do not create a separate news_sources table.
    }

    public function down(): void
    {
        Schema::dropIfExists('news_sources');
    }
};
