<?php

namespace Spatie\SiteSearch\Profiles;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\SiteSearch\Indexers\Indexer;

interface SearchProfile
{
    public function shouldIndex(UriInterface $url, ResponseInterface $response): bool;

    public function useIndexer(UriInterface $url, ResponseInterface $response): ?Indexer;
}
