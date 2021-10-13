<?php

namespace Spatie\SiteSearch\Events;

use Spatie\SiteSearch\Models\SiteSearchIndex;

class CreatedNewIndexEvent
{
    public function __construct(
        public string $newIndexName,
        public SiteSearchIndex $siteSearchIndex,
    ) {
    }
}
