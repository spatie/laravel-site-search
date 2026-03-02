<?php

namespace Spatie\SiteSearch\Profiles;

use Spatie\Crawler\Crawler;
use Spatie\Crawler\CrawlResponse;
use Spatie\SiteSearch\Indexers\Indexer;

interface SearchProfile
{
    public function shouldCrawl(string $url): bool;

    public function shouldIndex(string $url, CrawlResponse $response): bool;

    public function useIndexer(string $url, CrawlResponse $response): ?Indexer;

    public function configureCrawler(Crawler $crawler): void;
}
