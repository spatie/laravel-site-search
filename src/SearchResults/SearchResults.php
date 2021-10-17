<?php

namespace Spatie\SiteSearch\SearchResults;

use Illuminate\Support\Collection;

class SearchResults
{
    public function __construct(
        public Collection $hits,
        public int $processingTimeInMs,
        public int $totalCount,
        public int $limit,
        public int $offset,
    ) {
    }
}
