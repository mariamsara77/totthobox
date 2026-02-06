<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        // Visitors table indexes
        Schema::table('visitors', function (Blueprint $table) {
            $this->addIndexIfNotExists('visitors', 'visitors_created_country_device_idx', ['created_at', 'country', 'device'], $table);
            $this->addIndexIfNotExists('visitors', 'visitors_created_idx', ['created_at'], $table);
            $this->addIndexIfNotExists('visitors', 'visitors_country_idx', ['country'], $table);
            $this->addIndexIfNotExists('visitors', 'visitors_device_idx', ['device'], $table);
            $this->addIndexIfNotExists('visitors', 'visitors_hash_idx', ['hash'], $table);
            $this->addIndexIfNotExists('visitors', 'visitors_ip_idx', ['ip_address'], $table);
        });

        // Page views table indexes
        Schema::table('page_views', function (Blueprint $table) {
            $this->addIndexIfNotExists('page_views', 'page_views_visitor_created_idx', ['visitor_id', 'created_at'], $table);

            // সমাধান: এখানে URL এর পুরোটার বদলে প্রথম ২৫৫ ক্যারেক্টারের উপর ইনডেক্স করা হয়েছে
            if (!$this->indexExists('page_views', 'page_views_url_created_idx')) {
                DB::statement('CREATE INDEX page_views_url_created_idx ON page_views (url(255), created_at)');
            }

            $this->addIndexIfNotExists('page_views', 'page_views_created_idx', ['created_at'], $table);
            $this->addIndexIfNotExists('page_views', 'page_views_visitor_idx', ['visitor_id'], $table);
        });

        // Visitor sessions table indexes
        Schema::table('visitor_sessions', function (Blueprint $table) {
            $this->addIndexIfNotExists('visitor_sessions', 'sessions_visitor_created_idx', ['visitor_id', 'created_at'], $table);
            $this->addIndexIfNotExists('visitor_sessions', 'sessions_created_duration_idx', ['created_at', 'duration'], $table);
            $this->addIndexIfNotExists('visitor_sessions', 'sessions_created_idx', ['created_at'], $table);
        });
    }

    private function indexExists($tableName, $indexName)
    {
        $dbName = DB::connection()->getDatabaseName();
        $exists = DB::select("SELECT INDEX_NAME FROM INFORMATION_SCHEMA.STATISTICS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND INDEX_NAME = ?",
            [$dbName, $tableName, $indexName]
        );
        return !empty($exists);
    }

    private function addIndexIfNotExists($tableName, $indexName, $columns, $table)
    {
        if (!$this->indexExists($tableName, $indexName)) {
            $table->index($columns, $indexName);
        }
    }

    public function down()
    {
        // ড্রপ করার কোড আগের মতই থাকবে
    }
};
