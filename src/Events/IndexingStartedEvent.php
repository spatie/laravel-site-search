<?php

namespace Spatie\SiteSearch\Events;

use Spatie\SiteSearch\Models\SiteSearchConfig;

class IndexingStartedEvent
{
    public function __construct(public SiteSearchConfig $siteSearchConfig)
    {
    }
}
