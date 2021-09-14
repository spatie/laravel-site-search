<?php

namespace Spatie\SiteSearch;

use Spatie\Crawler\Crawler;
use Spatie\SiteSearch\Crawler\SearchProfileCrawlObserver;
use Spatie\SiteSearch\Crawler\SiteSearchCrawlProfile;
use Spatie\SiteSearch\Drivers\Driver;
use Spatie\SiteSearch\Profiles\SearchProfile;
use Spatie\SiteSearch\SearchResults\SearchResults;

class SiteSearch
{
    public function __construct(
        protected Driver $driver,
        protected SearchProfile $profile,
    ) {
    }

    public function crawl(string $baseUrl): self
    {
        $profile = new SiteSearchCrawlProfile($this->profile, $baseUrl);
        $observer = new SearchProfileCrawlObserver($this->profile, $this->driver);

        Crawler::create()
            ->setCrawlProfile($profile)
            ->setCrawlObserver($observer)
            ->startCrawling($baseUrl);

        return $this;
    }

    public function search(string $query): SearchResults
    {
        return $this->driver->search($query);
    }
}
