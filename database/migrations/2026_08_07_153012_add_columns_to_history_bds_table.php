<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('history_bds', function (Blueprint $table) {
            // Era / Category & Order
            $table->string('era')->nullable()->after('title')->index(); // e.g., ancient, british, liberation_war
            $table->integer('sort_order')->default(0)->after('is_featured')->index();

            // Timeline / Period Details
            $table->string('start_year', 50)->nullable()->after('description');
            $table->string('end_year', 50)->nullable()->after('start_year'); // Present হলে "বর্তমান" থাকবে

            // Additional Composite Index
            $table->index(['era', 'status', 'sort_order'], 'idx_history_era_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('history_bds', function (Blueprint $table) {
            $table->dropIndex('idx_history_era_order');
            $table->dropColumn([
                'era',
                'sort_order',
                'start_year',
                'end_year',
            ]);
        });
    }
};
