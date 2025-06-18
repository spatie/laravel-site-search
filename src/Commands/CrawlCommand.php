<?php

namespace Spatie\SiteSearch\Commands;

use Illuminate\Console\Command;
use Spatie\SiteSearch\Models\SiteSearchConfig;

class CrawlCommand extends Command
{
    public $signature = 'site-search:crawl {--sync}';

    public function handle()
    {
        $sync = $this->option('sync');
        $crawlSiteJob = config('site-search.crawl_site_job');

        SiteSearchConfig::enabled()
            ->each(function (SiteSearchConfig $siteSearchConfig) use ($sync, $crawlSiteJob) {
                $this->comment("Dispatching job to crawl `{$siteSearchConfig->crawl_url}`");

                if ($sync) {
                    dispatch_sync(new $crawlSiteJob($siteSearchConfig));
                } else {
                    dispatch(new $crawlSiteJob($siteSearchConfig));
                }
            });

        $this->info('All done');
    }
}
