<?php

namespace Spatie\SiteSearch;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Spatie\Crawler\Crawler;
use Spatie\SiteSearch\Crawler\SearchProfileCrawlObserver;
use Spatie\SiteSearch\Crawler\SiteSearchCrawlProfile;
use Spatie\SiteSearch\Drivers\Driver;
use Spatie\SiteSearch\Exceptions\SiteSearchIndexDoesNotExist;
use Spatie\SiteSearch\Models\SiteSearchIndex;
use Spatie\SiteSearch\Profiles\SearchProfile;
use Spatie\SiteSearch\SearchResults\SearchResults;

class SiteSearch
{
    public static function index(string $indexName): self
    {
        $siteSearchIndex = SiteSearchIndex::firstWhere('name', $indexName);

        if (! $siteSearchIndex) {
            throw SiteSearchIndexDoesNotExist::make($indexName);
        }

        return self::make($siteSearchIndex);
    }

    public static function make(SiteSearchIndex $siteSearchIndex): self
    {
        $driver = $siteSearchIndex->getDriver();

        $profile = $siteSearchIndex->getProfile();

        return new static($siteSearchIndex->index_name, $driver, $profile);
    }

    public function __construct(
        protected string $indexName,
        protected Driver $driver,
        protected SearchProfile $profile,
    ) {
    }

    public function crawl(string $baseUrl): self
    {
        $profile = new SiteSearchCrawlProfile($this->profile, $baseUrl);

        $observer = new SearchProfileCrawlObserver(
            $this->indexName,
            $this->profile,
            $this->driver
        );

        Crawler::create()
            ->setCrawlProfile($profile)
            ->setCrawlObserver($observer)
            ->startCrawling($baseUrl);

        return $this;
    }

    public function search(string $query, ?int $limit = null, ?int $offset = 0): SearchResults
    {
        return $this->driver->search($this->indexName, $query, $limit, $offset);
    }
}
