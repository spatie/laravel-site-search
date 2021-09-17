<?php

namespace Spatie\SiteSearch\Commands;

use Illuminate\Console\Command;
use Spatie\SiteSearch\Jobs\CrawlSiteJob;
use Spatie\SiteSearch\Support\SiteConfig;

class CrawlSitesCommand extends Command
{
    public $signature = 'site-search:crawl';

    public function handle()
    {
        collect(config('site-search.sites'))
            ->keys()
            ->each(function(string $siteConfigName) {
                $siteConfig = SiteConfig::make($siteConfigName);

                $this->comment("Dispatching job to crawl `{$siteConfig->url()}`");

                dispatch(new CrawlSiteJob($siteConfigName));
            });

        $this->info('All done');
    }
}
