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

        if ($this->hasDoNotIndexHeader($response)) {
            return false;
        }

        if ($this->urlShouldNotBeIndexed($url)) {
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

    protected function hasDoNotIndexHeader(ResponseInterface $response): bool
    {
        foreach (config('site-search.do_not_index_content_headers') as $headerName) {
            if ($response->hasHeader($headerName)) {
                return true;
            }
        }

        return false;
    }

    protected function urlShouldNotBeIndexed(UriInterface $url): bool
    {
        foreach (config('site-search.ignore_content_on_urls') as $configuredUrl) {
            if (fnmatch($configuredUrl, $url->getPath())) {
                return true;
            }
        }

        return false;
    }
}
