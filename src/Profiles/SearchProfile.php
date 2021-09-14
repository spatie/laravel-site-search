<?php

namespace Spatie\SiteSearch\Profiles;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\SiteSearch\Indexers\DefaultIndexer;

interface SearchProfile
{
    public function shouldIndex(UriInterface $url): bool;

    public function useIndexer(UriInterface $url, ResponseInterface $response): ?DefaultIndexer;
}
