<?php

namespace Spatie\SiteSearch\Events;

use Spatie\SiteSearch\Models\SiteSearchIndex;

class IndexingEndedEvent
{
    public function __construct(public SiteSearchIndex $siteSearchIndex) {}
}
