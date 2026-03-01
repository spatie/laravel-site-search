<?php

namespace Spatie\SiteSearch\Crawler;

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

    public function shouldCrawl(string $url): bool
    {
        if (! str_starts_with($url, $this->baseUrl)) {
            return false;
        }

        if ($this->isConfiguredNotToBeCrawled($url)) {
            return false;
        }

        return $this->profile->shouldCrawl($url);
    }

    protected function isConfiguredNotToBeCrawled(string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH);

        foreach (config('site-search.do_not_crawl_urls') as $configuredUrl) {
            if (fnmatch($configuredUrl, $path)) {
                return true;
            }
        }

        return false;
    }
}
