<?php

namespace Spatie\SiteSearch\Profiles;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\Crawler;
use Spatie\SiteSearch\Indexers\Indexer;

class DefaultSearchProfile implements SearchProfile
{
    public function shouldCrawl(UriInterface $url): bool
    {
        return true;
    }

    public function shouldIndex(UriInterface $url, ResponseInterface $response): bool
    {
        if ($response->getStatusCode() !== 200) {
            return false;
        }

        if ($response->hasHeader('site-search-do-not-index')) {
            return false;
        }

        return true;
    }

    public function useIndexer(UriInterface $url, ResponseInterface $response): ?Indexer
    {
        $defaultIndexer = config('site-search.default_indexer');

        return new $defaultIndexer($url, $response);
    }

    public function configureCrawler(Crawler $crawler): void
    {
    }
}
