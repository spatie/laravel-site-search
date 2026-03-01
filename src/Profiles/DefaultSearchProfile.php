<?php

namespace Spatie\SiteSearch\Profiles;

use Spatie\Crawler\Crawler;
use Spatie\Crawler\CrawlResponse;
use Spatie\SiteSearch\Indexers\Indexer;

class DefaultSearchProfile implements SearchProfile
{
    public function shouldCrawl(string $url): bool
    {
        return true;
    }

    public function shouldIndex(string $url, CrawlResponse $response): bool
    {
        if ($response->status() !== 200) {
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

    public function useIndexer(string $url, CrawlResponse $response): ?Indexer
    {
        $defaultIndexer = config('site-search.default_indexer');

        return new $defaultIndexer($url, $response);
    }

    public function configureCrawler(Crawler $crawler): void {}

    protected function hasDoNotIndexHeader(CrawlResponse $response): bool
    {
        foreach (config('site-search.do_not_index_content_headers') as $headerName) {
            if ($response->toPsrResponse()->hasHeader($headerName)) {
                return true;
            }
        }

        return false;
    }

    protected function urlShouldNotBeIndexed(string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH);

        foreach (config('site-search.ignore_content_on_urls') as $configuredUrl) {
            if (fnmatch($configuredUrl, $path)) {
                return true;
            }
        }

        return false;
    }
}
