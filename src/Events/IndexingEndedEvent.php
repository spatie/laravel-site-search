<?php

namespace Spatie\SiteSearch\Events;

use Spatie\SiteSearch\Models\SiteSearchConfig;

class IndexingEndedEvent
{
    public function __construct(public SiteSearchConfig $siteSearchConfig)
    {
    }
}
