<?php

namespace App\Search;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class GlobalSearchService
{
    private const MIN_TERM_LENGTH = 2;

    private const MAX_RESULTS_PER_MODEL = 30;

    private const MAX_TOTAL_RESULTS = 100;

    private const SHORT_TERM_THRESHOLD = 4;

    private const COLUMN_CACHE_KEY = 'search_column_existence_v2';

    private const COLUMN_CACHE_TTL_DAYS = 14;

    private const RESULT_CACHE_TTL = 60;

    private const FUZZY_MAX_RATIO = 0.34;

    private array $columnCache = [];

    public function search(string $rawTerm, ?int $limit = null): SearchResult
    {
        $term = trim($rawTerm);

        if (mb_strlen($term) < self::MIN_TERM_LENGTH) {
            return SearchResult::empty();
        }

        $limit = min($limit ?? self::MAX_TOTAL_RESULTS, self::MAX_TOTAL_RESULTS);

        $cacheKey = 'gs_result_v4_'.md5(mb_strtolower($term).'|'.$limit);

        return Cache::remember($cacheKey, self::RESULT_CACHE_TTL, function () use ($term, $limit) {

            // ── Prefix-scoped search ──────────────────────────────
            if (Str::contains($term, ':')) {
                [$rawPrefix, $query] = array_pad(explode(':', $term, 2), 2, '');
                $rawPrefix = trim($rawPrefix);
                $query = trim($query);

                $resolvedKey = SearchRegistry::resolvePrefix($rawPrefix);

                if ($resolvedKey) {
                    $config = SearchRegistry::get($resolvedKey);

                    // Empty query after prefix → still return scoped results
                    // (useful when user just clicks the filter button)
                    $searchTerm = $query !== '' ? $query : '*';

                    $items = $this->runSearch($config, $searchTerm === '*' ? '' : $searchTerm);

                    if ($searchTerm !== '' && $searchTerm !== '*') {
                        $items = $this->rankByRelevance($items, $searchTerm);
                        $items = $this->filterByMinScore($items, $searchTerm);
                    }

                    return new SearchResult($items->take($limit), $resolvedKey);
                }
            }

            // ── Global search ─────────────────────────────────────
            $items = collect();

            foreach (SearchRegistry::all() as $config) {
                $found = $this->runSearch($config, $term);
                $items = $items->merge($found);

                if ($items->count() >= $limit * 2) {
                    break;
                }
            }

            $items = $this->rankByRelevance($items, $term);
            $items = $this->filterByMinScore($items, $term);

            return new SearchResult($items->take($limit));
        });
    }

    // ══════════════════════════════════════════════
    // Core Search
    // ══════════════════════════════════════════════

    private function runSearch(array $config, string $term): Collection
    {
        $model = $config['model'];
        $relations = $config['relations'] ?? [];

        // Empty term (prefix only) → just take latest / top items
        if ($term === '') {
            try {
                $items = $model::query()
                    ->with($this->filterValidRelations($model, $relations))
                    ->latest('id')
                    ->take(self::MAX_RESULTS_PER_MODEL)
                    ->get();

                return $this->enrichItems($items, $config);
            } catch (\Throwable $e) {
                Log::warning('Empty-term search failed', ['model' => $model, 'error' => $e->getMessage()]);

                return collect();
            }
        }

        try {
            $items = $model::search($term)
                ->query(fn ($q) => $q->with($this->filterValidRelations($model, $relations)))
                ->take(self::MAX_RESULTS_PER_MODEL)
                ->get();

            // Short term or empty Scout result → DB fallback
            if ($items->isEmpty() || mb_strlen($term) <= self::SHORT_TERM_THRESHOLD) {
                $dbItems = $this->databaseSearch($model, $relations, $term);
                $items = $items->merge($dbItems)->unique('id');
            }

            return $this->enrichItems($items, $config);
        } catch (\Throwable $e) {
            Log::warning('Search engine unavailable — falling back to DB', [
                'model' => $model,
                'error' => $e->getMessage(),
            ]);

            $items = $this->databaseSearch($model, $relations, $term);

            return $this->enrichItems($items, $config);
        }
    }

    // ══════════════════════════════════════════════
    // Database Fallback
    // ══════════════════════════════════════════════

    private function databaseSearch(string $model, array $relations, string $term): Collection
    {
        $variants = $this->buildSearchVariants($term);
        $instance = new $model;
        $table = $instance->getTable();
        $validRelations = $this->filterValidRelations($model, $relations);

        $tokens = $this->tokenize($term);
        foreach ($tokens as $token) {
            if (mb_strlen($token) >= 2) {
                $variants[] = $token;
            }
        }
        $variants = array_values(array_unique($variants));

        return $model::query()
            ->with($validRelations)
            ->where(function ($query) use ($variants, $table) {
                foreach ($variants as $variant) {
                    $query->orWhere(function ($sub) use ($variant, $table) {
                        $this->applyLikeConditions($sub, $table, $variant);
                    });
                }
            })
            ->take(self::MAX_RESULTS_PER_MODEL)
            ->get();
    }

    private function applyLikeConditions($query, string $table, string $variant): void
    {
        $searchableColumns = [
            'name', 'title', 'bangla_name', 'english_name', 'arabic_name',
            'chapter_name', 'source_name', 'slug',
            'description', 'bio', 'details',
            'bangla_text', 'arabic_text', 'text_bangla', 'text_arabic',
            'text_english', 'bangla_meaning', 'bangla_fojilot', 'others',
        ];

        foreach ($searchableColumns as $column) {
            if ($this->columnExists($table, $column)) {
                $query->orWhere($column, 'like', "%{$variant}%");
            }
        }
    }

    // ══════════════════════════════════════════════
    // Advanced Relevance Ranking + Fuzzy
    // ══════════════════════════════════════════════

    private function rankByRelevance(Collection $items, string $term): Collection
    {
        $termLower = mb_strtolower(trim($term));
        $termNorm = $this->normalizeForCompare($termLower);
        $tokens = $this->tokenize($termLower);
        $tokenCount = count($tokens);

        $nameFields = [
            'bangla_name', 'english_name', 'arabic_name',
            'name', 'title', 'chapter_name', 'source_name',
        ];

        $textFields = [
            'bangla_text', 'arabic_text', 'text_bangla', 'text_arabic',
            'text_english', 'bangla_meaning', 'description', 'bio',
            'details', 'others',
        ];

        $deepFields = ['bangla_fojilot', 'tags'];

        return $items
            ->map(function ($item) use ($termLower, $termNorm, $tokens, $tokenCount, $nameFields, $textFields, $deepFields) {
                $score = 0;

                // Name blob
                $nameParts = [];
                foreach ($nameFields as $field) {
                    $v = mb_strtolower((string) ($item->{$field} ?? ''));
                    if ($v !== '') {
                        $nameParts[] = $v;
                    }
                }
                $displayTitle = mb_strtolower((string) ($item->_search_title ?? ''));
                if ($displayTitle !== '') {
                    $nameParts[] = $displayTitle;
                }
                $nameBlob = trim(implode(' ', $nameParts));
                $nameNorm = $this->normalizeForCompare($nameBlob);
                $slug = mb_strtolower((string) ($item->slug ?? ''));

                // 1. Exact / starts-with / contains
                if ($nameBlob !== '') {
                    if ($nameBlob === $termLower || $nameNorm === $termNorm) {
                        $score = 100;
                    } elseif (Str::startsWith($nameBlob, $termLower) || Str::startsWith($nameNorm, $termNorm)) {
                        $score = 92;
                    } elseif (Str::contains($nameBlob, $termLower) || Str::contains($nameNorm, $termNorm)) {
                        $score = 78;
                    }
                }

                // 2. Multi-word + fuzzy tokens
                if ($score < 100 && $tokenCount >= 1 && $nameBlob !== '') {
                    $matched = 0;
                    $fuzzyMatched = 0;

                    foreach ($tokens as $token) {
                        if ($token === '') {
                            continue;
                        }

                        $tokenNorm = $this->normalizeForCompare($token);

                        if (Str::contains($nameBlob, $token) || Str::contains($nameNorm, $tokenNorm)) {
                            $matched++;

                            continue;
                        }

                        if ($this->fuzzyTokenInText($token, $nameBlob) || $this->fuzzyTokenInText($tokenNorm, $nameNorm)) {
                            $fuzzyMatched++;
                        }
                    }

                    $strong = $matched;
                    $totalHit = $matched + $fuzzyMatched;

                    if ($totalHit > 0) {
                        $ratio = $totalHit / $tokenCount;

                        if ($strong === $tokenCount) {
                            $score = max($score, 88);
                        } elseif ($totalHit === $tokenCount) {
                            $score = max($score, 72);
                        } elseif ($ratio >= 0.6) {
                            $score = max($score, 55);
                        } elseif ($ratio >= 0.4) {
                            $score = max($score, 40);
                        } else {
                            $score = max($score, 28);
                        }
                    }

                    // Single-word fuzzy against whole name
                    if ($score < 70 && $tokenCount === 1) {
                        $best = $this->bestFuzzyScoreAgainstWords($tokens[0], $nameBlob);
                        if ($best >= 0.75) {
                            $score = max($score, 70);
                        } elseif ($best >= 0.60) {
                            $score = max($score, 50);
                        }
                    }
                }

                // 3. Slug
                if ($score < 78 && $slug !== '') {
                    $slugNorm = $this->normalizeForCompare($slug);

                    if ($slug === $termLower || Str::startsWith($slug, $termLower)) {
                        $score = max($score, 55);
                    } elseif (Str::contains($slug, $termLower) || Str::contains($slugNorm, $termNorm)) {
                        $score = max($score, 40);
                    } elseif ($tokenCount === 1 && $this->isFuzzyMatch($tokens[0] ?? $termLower, $slug)) {
                        $score = max($score, 38);
                    }
                }

                // 4. Text fields
                if ($score < 45) {
                    $textBlob = '';
                    foreach ($textFields as $field) {
                        $textBlob .= ' '.mb_strtolower((string) ($item->{$field} ?? ''));
                    }
                    $textBlob = trim($textBlob);
                    $textNorm = $this->normalizeForCompare($textBlob);

                    if ($textBlob !== '') {
                        if (Str::contains($textBlob, $termLower) || Str::contains($textNorm, $termNorm)) {
                            $score = max($score, 30);
                        } else {
                            $hit = 0;
                            foreach ($tokens as $token) {
                                if (Str::contains($textBlob, $token) || $this->fuzzyTokenInText($token, $textBlob)) {
                                    $hit++;
                                }
                            }
                            if ($tokenCount > 0 && $hit === $tokenCount) {
                                $score = max($score, 26);
                            } elseif ($tokenCount > 0 && ($hit / $tokenCount) >= 0.5) {
                                $score = max($score, 20);
                            }
                        }
                    }
                }

                // 5. Deep fields
                if ($score < 20) {
                    foreach ($deepFields as $field) {
                        $raw = $item->{$field} ?? null;
                        $value = is_array($raw)
                            ? mb_strtolower(implode(' ', $raw))
                            : mb_strtolower((string) $raw);

                        if ($value !== '' && (Str::contains($value, $termLower) || $this->fuzzyTokenInText($termLower, $value))) {
                            $score = max($score, 14);
                            break;
                        }
                    }
                }

                $item->_search_score = $score;

                return $item;
            })
            ->sortByDesc(fn ($item) => $item->_search_score)
            ->values();
    }

    private function filterByMinScore(Collection $items, string $term): Collection
    {
        $len = mb_strlen(trim($term));
        $minScore = $len <= 2 ? 55 : ($len <= 4 ? 28 : 18);

        return $items->filter(fn ($item) => ($item->_search_score ?? 0) >= $minScore)->values();
    }

    // ══════════════════════════════════════════════
    // Fuzzy + Bangla normalize helpers
    // ══════════════════════════════════════════════

    private function tokenize(string $term): array
    {
        $parts = preg_split('/[\s\-\_,\.]+/u', mb_strtolower(trim($term)), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_unique($parts));
    }

    private function normalizeForCompare(string $text): string
    {
        $text = mb_strtolower($text);

        $map = [
            'ঈ' => 'ই', 'ঊ' => 'উ', 'ঋ' => 'রি',
            'ষ' => 'শ', 'স' => 'শ',
            'ণ' => 'ন',
            'য' => 'জ',
            'ঢ়' => 'ঢ', 'ড়' => 'ড',
            'ঁ' => '', '়' => '',
            "'" => '', '"' => '',
        ];

        $text = strtr($text, $map);
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    private function isFuzzyMatch(string $a, string $b): bool
    {
        $a = $this->normalizeForCompare($a);
        $b = $this->normalizeForCompare($b);

        if ($a === '' || $b === '') {
            return false;
        }

        if ($a === $b || Str::contains($b, $a) || Str::contains($a, $b)) {
            return true;
        }

        $len = max(mb_strlen($a), mb_strlen($b));
        if ($len < 2) {
            return false;
        }

        similar_text($a, $b, $percent);

        if ($percent >= 72) {
            return true;
        }

        if (strlen($a) === mb_strlen($a) && strlen($b) === mb_strlen($b)) {
            $dist = levenshtein($a, $b);
            if ($dist >= 0 && ($dist / $len) <= self::FUZZY_MAX_RATIO) {
                return true;
            }
        }

        return false;
    }

    private function fuzzyTokenInText(string $token, string $text): bool
    {
        if ($token === '' || $text === '') {
            return false;
        }

        $words = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        foreach ($words as $word) {
            if ($this->isFuzzyMatch($token, $word)) {
                return true;
            }
        }

        return false;
    }

    private function bestFuzzyScoreAgainstWords(string $token, string $text): float
    {
        $token = $this->normalizeForCompare($token);
        $words = preg_split('/\s+/u', $this->normalizeForCompare($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $best = 0.0;

        foreach ($words as $word) {
            if ($word === $token) {
                return 1.0;
            }
            similar_text($token, $word, $percent);
            $best = max($best, $percent / 100);
        }

        return $best;
    }

    // ══════════════════════════════════════════════
    // Transliteration variants
    // ══════════════════════════════════════════════

    private function buildSearchVariants(string $term): array
    {
        $variants = [$term, $this->normalizeForCompare($term)];
        $isBangla = (bool) preg_match('/[\x{0980}-\x{09FF}]/u', $term);

        $map = function_exists('get_transliterations')
            ? (array) get_transliterations()
            : [];

        if (! empty($map)) {
            if ($isBangla) {
                foreach ($map as $bangla => $englishVariants) {
                    if (Str::contains($term, (string) $bangla)) {
                        foreach ((array) $englishVariants as $eng) {
                            $variants[] = $eng;
                        }
                    }
                }
            } else {
                $lowerTerm = mb_strtolower($term);
                foreach ($map as $bangla => $englishVariants) {
                    foreach ((array) $englishVariants as $eng) {
                        if (Str::contains($lowerTerm, mb_strtolower((string) $eng))) {
                            $variants[] = $bangla;
                            break;
                        }
                    }
                }
                $slug = Str::slug($term);
                if ($slug !== '' && $slug !== $term) {
                    $variants[] = $slug;
                }
            }
        }

        return array_values(array_unique(array_filter($variants)));
    }

    // ══════════════════════════════════════════════
    // Enrichment
    // ══════════════════════════════════════════════

    private function enrichItems(Collection $items, array $config): Collection
    {
        return $items->map(function ($item) use ($config) {
            $item->_search_title = $item->bangla_name
                ?? $item->english_name
                ?? $item->title
                ?? $item->name
                ?? $item->chapter_name
                ?? $item->arabic_name
                ?? 'No Title';

            $item->_search_image = $this->resolveImage($item);

            $item->_search_url = isset($config['route'])
                ? ($config['route'])($item)
                : ($item->url ?? '#');

            $item->_search_icon = $config['icon'] ?? 'document';
            $item->_search_label = $config['label'] ?? '';
            $item->_search_color = $config['color'] ?? 'zinc';
            $item->_search_subtitle = isset($config['subtitle'])
                ? ($config['subtitle'])($item)
                : '';

            return $item;
        });
    }

    private function resolveImage($item): string
    {
        if (method_exists($item, 'getFirstMediaUrl')) {
            $url = $item->getFirstMediaUrl('*', 'thumb')
                ?: $item->getFirstMediaUrl('*');

            if ($url) {
                return $url;
            }
        }

        return $item->image_url ?? asset('og-image.png');
    }

    // ══════════════════════════════════════════════
    // Helpers
    // ══════════════════════════════════════════════

    private function filterValidRelations(string $model, array $relations): array
    {
        if (empty($relations)) {
            return [];
        }

        $instance = new $model;

        return array_values(array_filter(
            $relations,
            fn ($relation) => method_exists($instance, $relation)
        ));
    }

    private function columnExists(string $table, string $column): bool
    {
        if (isset($this->columnCache[$table][$column])) {
            return $this->columnCache[$table][$column];
        }

        $persistedMap = Cache::get(self::COLUMN_CACHE_KEY, []);

        if (isset($persistedMap[$table][$column])) {
            $this->columnCache[$table][$column] = $persistedMap[$table][$column];

            return $persistedMap[$table][$column];
        }

        try {
            $exists = Schema::hasColumn($table, $column);
        } catch (\Throwable) {
            $exists = false;
        }

        $persistedMap[$table][$column] = $exists;
        $this->columnCache[$table][$column] = $exists;

        Cache::put(
            self::COLUMN_CACHE_KEY,
            $persistedMap,
            now()->addDays(self::COLUMN_CACHE_TTL_DAYS)
        );

        return $exists;
    }

    public static function clearCaches(): void
    {
        Cache::forget(self::COLUMN_CACHE_KEY);
    }
}
