<?php

namespace Spatie\SiteSearch\SearchResults;

class SearchResults
{
    public function __construct(
        public array $hits,
        public int $processingTimeInMs,
    ) {
    }
}
