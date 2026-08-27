<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Assumes you already ran the migration from
 * laravel-notification-channels/webpush (php artisan vendor:publish
 * --provider="NotificationChannels\WebPush\WebPushServiceProvider"
 * --tag="migrations"), which creates the base push_subscriptions table
 * (polymorphic "subscribable" columns + endpoint/public_key/auth_token).
 *
 * This migration only ADDS the extra columns we need for multi-device
 * management (device label + last-seen), without touching anything
 * the package relies on.
 */
return new class extends Migration
{
    public function up(): void
    {
        $connection = config('webpush.database_connection');
        $tableName = config('webpush.table_name', 'push_subscriptions');

        Schema::connection($connection)->table($tableName, function (Blueprint $table) use ($connection, $tableName) {
            if (! Schema::connection($connection)->hasColumn($tableName, 'device_label')) {
                $table->string('device_label')->nullable()->after('auth_token');
            }
            if (! Schema::connection($connection)->hasColumn($tableName, 'last_active_at')) {
                $table->timestamp('last_active_at')->nullable()->after('device_label');
            }
        });
    }

    public function down(): void
    {
        $connection = config('webpush.database_connection');
        $tableName = config('webpush.table_name', 'push_subscriptions');

        Schema::connection($connection)->table($tableName, function (Blueprint $table) {
            $table->dropColumn(['device_label', 'last_active_at']);
        });
    }
};
