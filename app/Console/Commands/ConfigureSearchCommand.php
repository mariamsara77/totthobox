<?php

namespace App\Console\Commands;

use App\Search\SearchRegistry;
use Illuminate\Console\Command;
use MeiliSearch\Client;

/**
 * php artisan search:configure
 *
 * Configures Meilisearch indexes for all registered models:
 *  - Filterable attributes
 *  - Sortable attributes
 *  - Ranking rules (with optional vector/AI ranking)
 *  - Typo tolerance (for both English and Bangla)
 *  - Stop words
 */
class ConfigureSearchCommand extends Command
{
    protected $signature   = 'search:configure {--fresh : Delete and recreate indexes}';
    protected $description = 'Configure Meilisearch indexes for all searchable models';

    public function handle(): int
    {
        $client = new Client(
            config('scout.meilisearch.host'),
            config('scout.meilisearch.key'),
        );

        foreach (SearchRegistry::all() as $key => $config) {
            $model     = app($config['model']);
            $indexName = $model->searchableAs();

            $this->info("Configuring index: {$indexName}");

            if ($this->option('fresh')) {
                try {
                    $client->deleteIndex($indexName);
                    $this->line("  → Deleted existing index.");
                } catch (\Exception) {
                    // Index didn't exist — fine.
                }
            }

            $index = $client->index($indexName);

            // ── Ranking rules (words first, then proximity, then exactness) ──
            $index->updateRankingRules([
                'words',
                'typo',
                'proximity',
                'attribute',
                'sort',
                'exactness',
            ]);

            // ── Typo tolerance (helps with misspellings in both scripts) ──
            $index->updateTypoTolerance([
                'enabled'            => true,
                'minWordSizeForTypos' => [
                    'oneTypo'  => 3,
                    'twoTypos' => 6,
                ],
            ]);

            // ── Searchable attributes (order = relevance weight) ──
            $index->updateSearchableAttributes([
                'title',
                'slug',
                'type',
                'description',
            ]);

            // ── Filterable ──
            $index->updateFilterableAttributes([
                'division_id',
                'district_id',
                'status',
                'is_featured',
            ]);

            // ── Sortable ──
            $index->updateSortableAttributes([
                'title',
                'id',
            ]);

            // ── Stop words (Bangla + English common words) ──
            $index->updateStopWords([
                'the', 'a', 'an', 'in', 'on', 'at', 'of', 'and', 'or', 'is', 'are',
                'এবং', 'বা', 'এর', 'এ', 'তে', 'কে', 'কি', 'না',
            ]);

            $this->line("  ✓ Configured.");
        }

        $this->newLine();
        $this->info('All search indexes configured successfully.');
        $this->line('Run <comment>php artisan scout:import</comment> to index your data.');

        return self::SUCCESS;
    }
}
