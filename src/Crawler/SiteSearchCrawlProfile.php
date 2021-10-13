<?php

namespace Spatie\SiteSearch\Crawler;

use Psr\Http\Message\UriInterface;
use Spatie\Crawler\CrawlProfiles\CrawlInternalUrls;
use Spatie\SiteSearch\Profiles\SearchProfile;

class SiteSearchCrawlProfile extends CrawlInternalUrls
{
    public function __construct(
        protected SearchProfile $profile,
        protected mixed $baseUrl,
    ) {
        parent::__construct($this->baseUrl);
    }

    public function shouldCrawl(UriInterface $url): bool
    {
        if (! str_starts_with((string)$url, (string)$this->baseUrl)) {
            return false;
        }

        return $this->profile->shouldCrawl($url);
    }
}
