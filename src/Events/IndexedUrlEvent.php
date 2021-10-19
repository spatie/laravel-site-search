<?php

namespace Spatie\SiteSearch\Events;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class IndexedUrlEvent
{
    public function __construct(
        UriInterface $url,
        ResponseInterface $response,
        ?UriInterface $foundOnUrl = null
    ) {
    }
}
