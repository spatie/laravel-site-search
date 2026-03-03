<?php

namespace Spatie\SiteSearch\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;
use Spatie\SiteSearch\Models\SiteSearchConfig;

class CrawlCommand extends Command implements Isolatable
{
    public $signature = 'site-search:crawl {--sync}';

    public function handle(): void
    {
        $sync = $this->option('sync');
        $crawlSiteJob = config('site-search.crawl_site_job');

        SiteSearchConfig::enabled()
            ->each(function (SiteSearchConfig $siteSearchConfig) use ($sync, $crawlSiteJob) {
                $this->comment("Dispatching job to crawl `{$siteSearchConfig->crawl_url}`");

                $dispatchFunction = $sync ? dispatch_sync(...) : dispatch(...);

                $dispatchFunction(new $crawlSiteJob($siteSearchConfig));
            });

        $this->info('All done');
    }
}
