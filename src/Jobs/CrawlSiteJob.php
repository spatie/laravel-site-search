<?php

namespace Spatie\SiteSearch\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\SiteSearch\SiteSearch;
use Spatie\SiteSearch\Support\SiteConfig;

class CrawlSiteJob implements ShouldQueue
{
    public function __construct(
        public string $siteConfigName
    ) {}

    public function handle()
    {
        $siteConfig = SiteConfig::make($this->siteConfigName);

        $siteSearch = SiteSearch::make($this->siteConfigName);

        $siteSearch->crawl($siteConfig->url());
    }
}
