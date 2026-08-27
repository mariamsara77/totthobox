<?php

namespace App\Console\Commands;

use App\Services\AutonomousContentAgent;
use Illuminate\Console\Command;

class AutoImproveContent extends Command
{
    protected $signature = 'content:auto-improve
                            {--model= : শুধু একটা model run করো, e.g. --model=TourismBd}
                            {--dry-run : কিছু insert করবে না, শুধু stats দেখাবে}';

    protected $description = 'Autonomous AI agent — scans all models, fills gaps, remakes weak records';

    public function handle(AutonomousContentAgent $agent): int
    {
        $this->components->info('🤖 AutonomousContentAgent starting...');

        $logger = function (string $msg) {
            $this->line($msg);
        };

        // ── Single model mode ──────────────────────────────────────────────
        if ($model = $this->option('model')) {
            $registry = $this->modelRegistry();

            if (!isset($registry[$model])) {
                $this->components->error("Model '{$model}' is not registered. Available: " . implode(', ', array_keys($registry)));
                return self::FAILURE;
            }

            if ($this->option('dry-run')) {
                $this->showStats($agent);
                return self::SUCCESS;
            }

            try {
                $result = $agent->runForModel($registry[$model], $logger);
                $this->components->success("Done: +{$result['generated']} generated, {$result['remade']} remade");
            } catch (\Throwable $e) {
                $this->components->error($e->getMessage());
                return self::FAILURE;
            }

            return self::SUCCESS;
        }

        // ── Dry run ────────────────────────────────────────────────────────
        if ($this->option('dry-run')) {
            $this->showStats($agent);
            return self::SUCCESS;
        }

        // ── Full cycle ─────────────────────────────────────────────────────
        $result = $agent->runFullCycle($logger);

        $this->newLine();
        $this->components->twoColumnDetail('Models processed', $result['models_processed']);
        $this->components->twoColumnDetail('Total generated', $result['total_generated']);
        $this->components->twoColumnDetail('Total remade', $result['total_remade']);
        $this->components->twoColumnDetail('Duration', $result['duration_seconds'] . 's');

        return self::SUCCESS;
    }

    private function showStats(AutonomousContentAgent $agent): void
    {
        $stats = $agent->getModelStats();

        $this->table(
            ['Model', 'Table', 'Rows', 'Min Required', 'Needs Gen?', 'Avg Quality'],
            array_map(fn($s) => [
                $s['display_name'],
                $s['table'],
                number_format($s['count']),
                $s['min_rows'],
                $s['needs_generation'] ? '⚠ YES' : '✓ OK',
                $s['avg_quality_score'] . '%',
            ], $stats)
        );

        $lastRun = $agent->getLastRunInfo();
        if ($lastRun) {
            $this->line("Last run: {$lastRun['ran_at']} ({$lastRun['duration_seconds']}s)");
        }
    }

    private function modelRegistry(): array
    {
        return [
            // Location & Administrative
            'Division' => \App\Models\Division::class,
            'District' => \App\Models\District::class,
            'Thana' => \App\Models\Thana::class,

            // Core Content Models
            'TourismBd' => \App\Models\TourismBd::class,
            'HistoryBd' => \App\Models\HistoryBd::class,
            'IntroBd' => \App\Models\IntroBd::class,
            'EstablishmentBd' => \App\Models\EstablishmentBd::class,
            'Hospital' => \App\Models\Hospital::class,

            // Religious & Educational
            'BasicIslam' => \App\Models\BasicIslam::class,
            'Sura' => \App\Models\Sura::class,
            'Dowa' => \App\Models\Dowa::class,
            'Quran' => \App\Models\Quran::class,
            'Subject' => \App\Models\Subject::class,
            'ClassLevel' => \App\Models\ClassLevel::class,
            'ExcelTutorial' => \App\Models\ExcelTutorial::class,

            // Health & Food
            'BasicHealth' => \App\Models\BasicHealth::class,
            'Food' => \App\Models\Food::class,
            'Nutrient' => \App\Models\Nutrient::class,
            'FoodNutrient' => \App\Models\FoodNutrient::class,

            // People & Contacts
            'Person' => \App\Models\Person::class,
            'Position' => \App\Models\Position::class,
            'ContactNumber' => \App\Models\ContactNumber::class,
            'PeopleCategory' => \App\Models\PeopleCategory::class,

            // Business & News
            'BuySellItem' => \App\Models\BuySellItem::class,
            'BuySellPost' => \App\Models\BuySellPost::class,
            'NewsHeading' => \App\Models\NewsHeading::class,

            // Others
            'Holiday' => \App\Models\Holiday::class,
            'Sign' => \App\Models\Sign::class,
            'Question' => \App\Models\Question::class,
            'Test' => \App\Models\Test::class,
        ];
    }
}