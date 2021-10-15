<?php

namespace Spatie\SiteSearch\Events;

use Spatie\SiteSearch\Models\SiteSearchConfig;

class NewIndexCreatedEvent
{
    public function __construct(
        public string $newIndexName,
        public SiteSearchConfig $siteSearchConfig,
    ) {
    }
}
