<?php

namespace App\Console\Commands;

use App\Models\NewsHeading;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Cross-source story clustering — AdSense value-add feature.
 * Does NOT generate new text. Only links existing headlines by title similarity.
 *
 * Schedule (routes/console.php):
 *   Schedule::command('news:cluster-stories')->everyFifteenMinutes()
 *       ->withoutOverlapping(10)
 *       ->runInBackground()
 *       ->appendOutputTo(storage_path('logs/news-cluster.log'));
 */
class ClusterNewsStories extends Command
{
    protected $signature = 'news:cluster-stories
                            {--hours=48 : Look back window}
                            {--threshold=0.48 : Jaccard threshold (0.4–0.6 recommended)}
                            {--dry-run : Only report, no DB write}';

    protected $description = 'সাম্প্রতিক হেডলাইনগুলোকে টাইটেল-সাদৃশ্যের ভিত্তিতে একই story_group-এ যুক্ত করে';

    private const STOPWORDS = [
        // Bangla
        'এবং', 'করে', 'করেছে', 'করেছেন', 'হয়েছে', 'হয়েছেন', 'জানিয়েছে', 'জানান', 'বলেন', 'তবে',
        'পরে', 'সঙ্গে', 'নিয়ে', 'থেকে', 'জন্য', 'কারণে', 'দিয়ে', 'সাথে', 'কাছে', 'মধ্যে', 'আজ',
        'গতকাল', 'বাংলাদেশে', 'বাংলাদেশের', 'নিয়ে', 'হয়েছে', 'হয়েছেন', 'করা', 'করেন',
        // English
        'the', 'a', 'an', 'and', 'of', 'to', 'in', 'on', 'for', 'with', 'is', 'are', 'was', 'were',
        'by', 'at', 'from', 'as', 'that', 'this', 'it', 'be', 'has', 'have', 'had', 'will', 'said',
    ];

    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $threshold = (float) $this->option('threshold');
        $dryRun = (bool) $this->option('dry-run');
        $cutoff = now()->subHours($hours);

        $candidates = NewsHeading::query()
            ->where(function ($q) use ($cutoff) {
                $q->where('published_at', '>=', $cutoff)
                    ->orWhere(function ($q2) use ($cutoff) {
                        $q2->whereNull('published_at')->where('created_at', '>=', $cutoff);
                    });
            })
            ->orderByRaw('COALESCE(published_at, created_at) ASC')
            ->get(['id', 'title', 'language', 'source_key', 'story_group']);

        $this->info("Comparing {$candidates->count()} headlines (last {$hours}h, threshold {$threshold})".($dryRun ? ' [DRY-RUN]' : ''));

        $joined = 0;

        // Language groups separately — Bangla ↔ English word-overlap is unreliable
        foreach ($candidates->groupBy('language') as $lang => $items) {
            $items = $items->values();
            $n = $items->count();

            for ($i = 0; $i < $n; $i++) {
                $a = $items[$i];

                // Already grouped → still allow attaching new unmatched items to existing group
                $wordsA = $this->significantWords($a->title);
                if (count($wordsA) < 3) {
                    continue;
                }

                for ($j = $i + 1; $j < $n; $j++) {
                    $b = $items[$j];

                    if ($a->source_key === $b->source_key) {
                        continue; // same outlet ≠ “different coverage”
                    }

                    // Both already in same group → skip
                    if ($a->story_group && $a->story_group === $b->story_group) {
                        continue;
                    }

                    $wordsB = $this->significantWords($b->title);
                    if (count($wordsB) < 3) {
                        continue;
                    }

                    $score = $this->jaccardSimilarity($wordsA, $wordsB);

                    // Light boost when both titles share a longer token (≥5 chars)
                    if ($score >= ($threshold - 0.08) && $this->hasLongTokenOverlap($wordsA, $wordsB)) {
                        $score += 0.06;
                    }

                    if ($score >= $threshold) {
                        $groupId = $a->story_group ?: $b->story_group ?: (string) Str::uuid();

                        if (! $dryRun) {
                            NewsHeading::whereIn('id', [$a->id, $b->id])->update(['story_group' => $groupId]);
                        }

                        $a->story_group = $groupId;
                        $b->story_group = $groupId;
                        $joined++;

                        if ($this->output->isVerbose()) {
                            $this->line("  [{$score}] {$a->source_key} ↔ {$b->source_key}");
                        }
                    }
                }
            }
        }

        $this->info("Done. New links created: {$joined}");

        return self::SUCCESS;
    }

    private function significantWords(string $title): array
    {
        $clean = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $title);
        $words = preg_split('/\s+/u', mb_strtolower($clean), -1, PREG_SPLIT_NO_EMPTY);

        return array_values(array_filter(
            $words,
            fn ($w) => mb_strlen($w) >= 3 && ! in_array($w, self::STOPWORDS, true)
        ));
    }

    private function jaccardSimilarity(array $a, array $b): float
    {
        $setA = array_unique($a);
        $setB = array_unique($b);

        $intersection = count(array_intersect($setA, $setB));
        $union = count(array_unique(array_merge($setA, $setB)));

        return $union > 0 ? $intersection / $union : 0.0;
    }

    private function hasLongTokenOverlap(array $a, array $b): bool
    {
        $longA = array_filter($a, fn ($w) => mb_strlen($w) >= 5);
        $longB = array_filter($b, fn ($w) => mb_strlen($w) >= 5);

        return count(array_intersect($longA, $longB)) >= 1;
    }
}
