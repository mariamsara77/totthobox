<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Legacy duplicate of 2026_03_14_165651_create_telescope_entries_table.
 *
 * Keep this migration filename for deployed migration history, but do not
 * recreate or drop tables owned by the original migration.
 */
return new class extends Migration
{
    public function getConnection(): ?string
    {
        return config('telescope.storage.database.connection');
    }

    public function up(): void
    {
        // The earlier canonical migration creates these tables already.
        // This duplicate intentionally performs no schema changes.
    }

    public function down(): void
    {
        // The earlier canonical migration owns teardown of these tables.
    }
};
