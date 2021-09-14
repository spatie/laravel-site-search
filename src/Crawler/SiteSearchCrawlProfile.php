<?php

namespace Spatie\SiteSearch\Crawler;

use Psr\Http\Message\UriInterface;
use Spatie\Crawler\CrawlProfiles\CrawlProfile;
use Spatie\SiteSearch\Profiles\SearchProfile;

class SiteSearchCrawlProfile extends CrawlProfile
{
    public function __construct(
        protected SearchProfile $profile
    ) {}

    public function shouldCrawl(UriInterface $url): bool
    {
        return $this->profile->shouldIndex($url);
    }
}
