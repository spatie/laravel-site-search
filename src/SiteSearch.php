<?php

namespace Spatie\SiteSearch;

use Spatie\Crawler\Crawler;
use Spatie\SiteSearch\Crawler\SearchProfileCrawlObserver;
use Spatie\SiteSearch\Crawler\SiteSearchCrawlProfile;
use Spatie\SiteSearch\Drivers\Driver;
use Spatie\SiteSearch\Models\SiteSearchIndex;
use Spatie\SiteSearch\Profiles\SearchProfile;
use Spatie\SiteSearch\SearchResults\SearchResults;

class SiteSearch
{
    public static function make(SiteSearchIndex $siteSearchIndex)
    {
        $driver = $siteSearchIndex->getDriver();

        $profile = $siteSearchIndex->getProfile();

        return new static($driver, $profile);
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
        $observer = new SearchProfileCrawlObserver($this->indexName, $this->profile, $this->driver);

        Crawler::create()
            ->setCrawlProfile($profile)
            ->setCrawlObserver($observer)
            ->startCrawling($baseUrl);

        return $this;
    }

    public function search(string $query): SearchResults
    {
        return $this->driver->search($this->indexName, $query);
    }
}
