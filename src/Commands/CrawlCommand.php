<?php

namespace Spatie\SiteSearch\Commands;

use Illuminate\Console\Command;
use Spatie\SiteSearch\Jobs\CrawlSiteJob;
use Spatie\SiteSearch\Models\SiteSearchConfig;

class CrawlCommand extends Command
{
    public $signature = 'site-search:crawl';

    public function handle()
    {
        SiteSearchConfig::enabled()
            ->each(function (SiteSearchConfig $siteSearchConfig) {
                $this->comment("Dispatching job to crawl `{$siteSearchConfig->crawl_url}`");

                dispatch(new CrawlSiteJob($siteSearchConfig));
            });

        $this->info('All done');
    }
}
