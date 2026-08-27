<?php

namespace App\Search;

use Illuminate\Support\Collection;

/**
 * SearchResult — immutable value object returned by GlobalSearchService.
 */
final class SearchResult
{
    public readonly Collection $items;
    public readonly ?string $scope;   // e.g. "tourism" if prefix search was used
    public readonly bool $isEmpty;

    public function __construct(Collection $items, ?string $scope = null)
    {
        $this->items   = $items;
        $this->scope   = $scope;
        $this->isEmpty = $items->isEmpty();
    }

    public static function empty(): self
    {
        return new self(collect(), null);
    }

    public function count(): int
    {
        return $this->items->count();
    }
}
