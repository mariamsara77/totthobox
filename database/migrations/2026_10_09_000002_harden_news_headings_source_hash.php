<?php

use IlluminateDatabaseMigrationsMigration;
use IlluminateDatabaseSchemaBlueprint;
use IlluminateSupportFacadesDB;
use IlluminateSupportFacadesSchema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_headings', function (Blueprint $table) {
            $table->char('source_hash', 64)->nullable()->after('source_link');
            $table->unique('source_hash');
        });

        DB::table('news_headings')
            ->select(['id', 'source_link'])
            ->whereNull('source_hash')
            ->orderBy('id')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    $sourceLink = rtrim((string) $row->source_link, '/');
                    $hash = hash('sha256', $sourceLink);

                    $alreadyUsed = DB::table('news_headings')
                        ->where('source_hash', $hash)
                        ->exists();

                    if ($alreadyUsed) {
                        continue;
                    }

                    DB::table('news_headings')
                        ->where('id', $row->id)
                        ->update(['source_hash' => $hash]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('news_headings', function (Blueprint $table) {
            $table->dropUnique(['source_hash']);
            $table->dropColumn('source_hash');
        });
    }
};
