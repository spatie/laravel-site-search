<?php

namespace Spatie\SiteSearch\Events;

use Spatie\Crawler\CrawlProgress;
use Spatie\Crawler\Enums\FinishReason;

class CrawlFinishedEvent
{
    public function __construct(
        public FinishReason $finishReason,
        public CrawlProgress $progress,
    ) {
    }
}
