<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    /**
     * Remove only the redundant catalogue table. Existing news_headings rows
     * and their image fields remain untouched.
     */
    public function up(): void
    {
        Schema::dropIfExists('news_sources');
    }

    public function down(): void
    {
        // The canonical source catalogue now lives in config/news_sources.php.
        // Recreating an empty table would restore an obsolete dependency.
    }
};
