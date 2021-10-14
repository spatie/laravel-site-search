<?php

namespace Spatie\SiteSearch\SearchResults;

use Illuminate\Support\Collection;

class SearchResults
{
    public function __construct(
        public Collection $hits,
        public int $processingTimeInMs,
    ) {
    }
}
