<?php

namespace Spatie\SiteSearch\Events;

use GuzzleHttp\Exception\RequestException;
use Spatie\Crawler\CrawlProgress;

class FailedToCrawlUrlEvent
{
    public function __construct(
        public string $url,
        public RequestException $requestException,
        public CrawlProgress $progress,
        public ?string $foundOnUrl = null,
    ) {}
}
