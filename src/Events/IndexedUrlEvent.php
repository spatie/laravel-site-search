<?php

namespace Spatie\SiteSearch\Events;

use Spatie\Crawler\CrawlProgress;
use Spatie\Crawler\CrawlResponse;

class IndexedUrlEvent
{
    public function __construct(
        public string $url,
        public CrawlResponse $response,
        public CrawlProgress $progress,
        public ?string $foundOnUrl = null,
    ) {}
}
