<?php

namespace App\Console\Commands;

use App\Models\NewsHeading;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Cross-source story clustering — AdSense value-add feature.
 * Does NOT generate new text. Only links existing headlines by title similarity.
 */
class ClusterNewsStories extends Command
{
    protected $signature = 'news:cluster-stories
                            {--hours=48 : Look back window}
                            {--threshold=0.48 : Jaccard threshold (0.42–0.55 recommended)}
                            {--dry-run : Only report, no DB write}';

    protected $description = 'সাম্প্রতিক হেডলাইনগুলোকে টাইটেল-সাদৃশ্যের ভিত্তিতে একই story_group-এ যুক্ত করে';

    private const STOPWORDS = [
        // Bangla
        'এবং',
        'করে',
        'করেছে',
        'করেছেন',
        'হয়েছে',
        'হয়েছেন',
        'জানিয়েছে',
        'জানান',
        'বলেন',
        'তবে',
        'পরে',
        'সঙ্গে',
        'নিয়ে',
        'থেকে',
        'জন্য',
        'কারণে',
        'দিয়ে',
        'সাথে',
        'কাছে',
        'মধ্যে',
        'আজ',
        'গতকাল',
        'বাংলাদেশে',
        'বাংলাদেশের',
        'নিয়ে',
        'হয়েছে',
        'হয়েছেন',
        'করা',
        'করেন',
        'এর',
        'ও',
        'কিন্তু',
        'হলে',
        'হবে',
        'হয়',
        'হচ্ছে',
        'করেছি',
        'করেছিল',
        'বলেছে',
        'বলেছেন',
        'জানায়',
        'জানায়',
        'এক',
        'একটি',
        'সে',
        'তিনি',
        'আর',
        'বা',
        'যে',
        'যা',
        'কি',
        'কে',
        'কেন',

        // English
        'the',
        'a',
        'an',
        'and',
        'of',
        'to',
        'in',
        'on',
        'for',
        'with',
        'is',
        'are',
        'was',
        'were',
        'by',
        'at',
        'from',
        'as',
        'that',
        'this',
        'it',
        'be',
        'has',
        'have',
        'had',
        'will',
        'said',
        'says',
        'after',
        'over',
        'into',
        'also',
        'more',
        'than',
        'about',
        'up',
        'out',
    ];

    public function handle(): int
    {
        $hours     = (int) $this->option('hours');
        $threshold = (float) $this->option('threshold');
        $dryRun    = (bool) $this->option('dry-run');
        $cutoff    = now()->subHours($hours);

        // শুধুমাত্র যেগুলো এখনো কোনো গ্রুপে নেই + সাম্প্রতিক
        $ungrouped = NewsHeading::query()
            ->whereNull('story_group')
            ->where(function ($q) use ($cutoff) {
                $q->where('published_at', '>=', $cutoff)
                    ->orWhere(function ($q2) use ($cutoff) {
                        $q2->whereNull('published_at')
                            ->where('created_at', '>=', $cutoff);
                    });
            })
            ->orderByRaw('COALESCE(published_at, created_at) ASC')
            ->get(['id', 'title', 'language', 'source_key', 'story_group']);

        // ইতিমধ্যে গ্রুপ করা আইটেমগুলো (ম্যাচিং এর জন্য)
        $grouped = NewsHeading::query()
            ->whereNotNull('story_group')
            ->where(function ($q) use ($cutoff) {
                $q->where('published_at', '>=', $cutoff)
                    ->orWhere(function ($q2) use ($cutoff) {
                        $q2->whereNull('published_at')
                            ->where('created_at', '>=', $cutoff);
                    });
            })
            ->get(['id', 'title', 'language', 'source_key', 'story_group']);

        $this->info(sprintf(
            'Ungrouped: %d | Already grouped: %d | Threshold: %s%s',
            $ungrouped->count(),
            $grouped->count(),
            $threshold,
            $dryRun ? ' [DRY-RUN]' : ''
        ));

        $joined = 0;

        // Language অনুযায়ী আলাদা করে কাজ করব
        foreach ($ungrouped->groupBy('language') as $lang => $items) {
            $items = $items->values();
            $existingInLang = $grouped->where('language', $lang)->values();

            foreach ($items as $newItem) {
                $wordsA = $this->significantWords($newItem->title);

                if (count($wordsA) < 3) {
                    continue;
                }

                $bestScore   = 0;
                $bestGroupId = null;
                $bestMatch   = null;

                // ১. আগে থেকে থাকা গ্রুপগুলোর সাথে মিলিয়ে দেখি
                foreach ($existingInLang as $existing) {
                    if ($newItem->source_key === $existing->source_key) {
                        continue;
                    }

                    $wordsB = $this->significantWords($existing->title);
                    if (count($wordsB) < 3) {
                        continue;
                    }

                    $score = $this->jaccardSimilarity($wordsA, $wordsB);

                    if ($score >= ($threshold - 0.06) && $this->hasLongTokenOverlap($wordsA, $wordsB)) {
                        $score = min(1.0, $score + 0.05);
                    }

                    if ($score > $bestScore && $score >= $threshold) {
                        $bestScore   = $score;
                        $bestGroupId = $existing->story_group;
                        $bestMatch   = $existing;
                    }
                }

                // ২. যদি কোনো পুরনো গ্রুপের সাথে না মিলে, তাহলে অন্য ungrouped আইটেমের সাথে চেষ্টা করি
                if (! $bestGroupId) {
                    foreach ($items as $other) {
                        if ($other->id === $newItem->id) {
                            continue;
                        }
                        if ($newItem->source_key === $other->source_key) {
                            continue;
                        }
                        if ($other->story_group) {
                            // ইতিমধ্যে এই রানে গ্রুপ হয়ে গেছে
                            $score = $this->jaccardSimilarity($wordsA, $this->significantWords($other->title));
                            if ($score >= $threshold) {
                                $bestScore   = $score;
                                $bestGroupId = $other->story_group;
                                $bestMatch   = $other;
                                break;
                            }
                            continue;
                        }

                        $wordsB = $this->significantWords($other->title);
                        if (count($wordsB) < 3) {
                            continue;
                        }

                        $score = $this->jaccardSimilarity($wordsA, $wordsB);

                        if ($score >= ($threshold - 0.06) && $this->hasLongTokenOverlap($wordsA, $wordsB)) {
                            $score = min(1.0, $score + 0.05);
                        }

                        if ($score > $bestScore && $score >= $threshold) {
                            $bestScore   = $score;
                            $bestGroupId = (string) Str::uuid();
                            $bestMatch   = $other;
                        }
                    }
                }

                if ($bestGroupId && $bestMatch) {
                    if (! $dryRun) {
                        $idsToUpdate = [$newItem->id];

                        // যদি নতুন গ্রুপ তৈরি হয়, তাহলে অন্য আইটেমটাকেও আপডেট করি
                        if (! $bestMatch->story_group) {
                            $idsToUpdate[] = $bestMatch->id;
                        }

                        NewsHeading::whereIn('id', $idsToUpdate)
                            ->update(['story_group' => $bestGroupId]);
                    }

                    // মেমোরিতে আপডেট রাখি যাতে পরবর্তী আইটেম ব্যবহার করতে পারে
                    $newItem->story_group = $bestGroupId;
                    $bestMatch->story_group = $bestGroupId;

                    $joined++;

                    if ($this->output->isVerbose()) {
                        $this->line(sprintf(
                            '  [%.2f] %s ↔ %s',
                            $bestScore,
                            $newItem->source_key,
                            $bestMatch->source_key
                        ));
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
            fn($w) => mb_strlen($w) >= 3 && ! in_array($w, self::STOPWORDS, true)
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
        $longA = array_filter($a, fn($w) => mb_strlen($w) >= 5);
        $longB = array_filter($b, fn($w) => mb_strlen($w) >= 5);

        return count(array_intersect($longA, $longB)) >= 1;
    }
}