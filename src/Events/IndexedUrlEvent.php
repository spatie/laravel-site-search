<?php

namespace Spatie\SiteSearch\Events;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class IndexedUrlEvent
{
    public function __construct(
        public UriInterface $url,
        public ResponseInterface $response,
        public ?UriInterface $foundOnUrl = null
    ) {
    }
}
