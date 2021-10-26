<?php

namespace Spatie\SiteSearch\Events;

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\UriInterface;

class FailedToCrawlUrlEvent
{
    public function __construct(
        public UriInterface $url,
        public RequestException $requestException,
        public ?UriInterface $foundOnUrl = null
    ) {
    }
}
