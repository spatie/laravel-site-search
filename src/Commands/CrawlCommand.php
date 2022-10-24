<?php

namespace Spatie\SiteSearch\Commands;

use Illuminate\Console\Command;
use Spatie\SiteSearch\Models\SiteSearchConfig;

class CrawlCommand extends Command
{
    public $signature = 'site-search:crawl';

    public function handle()
    {
        $crawlSiteJob = config('site-search.crawl_site_job');

        SiteSearchConfig::enabled()
            ->each(function (SiteSearchConfig $siteSearchConfig) use ($crawlSiteJob) {
                $this->comment("Dispatching job to crawl `{$siteSearchConfig->crawl_url}`");

                dispatch(new $crawlSiteJob($siteSearchConfig));
            });

        $this->info('All done');
    }
}
