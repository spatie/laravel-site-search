<?php

namespace Spatie\SiteSearch\Profiles;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\Crawler;
use Spatie\SiteSearch\Indexers\Indexer;

interface SearchProfile
{
    public function shouldCrawl(UriInterface $url): bool;

    public function shouldIndex(UriInterface $url, ResponseInterface $response): bool;

    public function useIndexer(UriInterface $url, ResponseInterface $response): ?Indexer;

    public function configureCrawler(Crawler $crawler): void;
}
