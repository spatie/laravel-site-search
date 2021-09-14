<?php

namespace Spatie\SiteSearch;

use Spatie\Crawler\Crawler;
use Spatie\SiteSearch\Crawler\SearchProfileCrawlObserver;
use Spatie\SiteSearch\Crawler\SiteSearchCrawlProfile;
use Spatie\SiteSearch\Drivers\Driver;
use Spatie\SiteSearch\Profiles\SearchProfile;

class SiteSearch
{
    public function __construct(
        protected Driver $driver,
        protected SearchProfile $profile,
    )
    {

    }

    public function crawl(string $url): self
    {
        $profile = new SiteSearchCrawlProfile($this->profile);
        $observer = new SearchProfileCrawlObserver($this->profile, $this->driver);

        Crawler::create()
            ->setCrawlProfile($profile)
            ->setCrawlObserver($observer)
            ->startCrawling($url);


        return $this;
    }
}
