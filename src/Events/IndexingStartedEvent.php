<?php

namespace Spatie\SiteSearch\Events;

use Spatie\SiteSearch\Models\SiteSearchIndex;

class IndexingStartedEvent
{
    public function __construct(public SiteSearchIndex $siteSearchIndex)
    {
    }
}
