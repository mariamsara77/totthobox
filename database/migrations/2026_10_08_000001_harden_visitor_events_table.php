<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitor_events', function (Blueprint $table) {
            if (! Schema::hasColumn('visitor_events', 'visitor_id')) {
                $table->foreignId('visitor_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('visitors')
                    ->cascadeOnDelete()
                    ->index();
            }

            if (! Schema::hasColumn('visitor_events', 'event_uuid')) {
                $table->uuid('event_uuid')
                    ->nullable()
                    ->after('visitor_id')
                    ->unique();
            }
        });
    }

    public function down(): void
    {
        Schema::table('visitor_events', function (Blueprint $table) {
            if (Schema::hasColumn('visitor_events', 'event_uuid')) {
                $table->dropUnique(['event_uuid']);
                $table->dropColumn('event_uuid');
            }

            if (Schema::hasColumn('visitor_events', 'visitor_id')) {
                $table->dropForeign(['visitor_id']);
                $table->dropIndex(['visitor_id']);
                $table->dropColumn('visitor_id');
            }
        });
    }
};
