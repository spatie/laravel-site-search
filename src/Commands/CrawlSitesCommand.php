<?php

namespace Spatie\SiteSearch\Commands;

use Illuminate\Console\Command;
use Spatie\SiteSearch\Jobs\CrawlSiteJob;
use Spatie\SiteSearch\Models\SiteSearchIndex;

class CrawlSitesCommand extends Command
{
    public $signature = 'site-search:crawl';

    public function handle()
    {
        SiteSearchIndex::enabled()
            ->each(function (SiteSearchIndex $siteSearchIndex) {
                $this->comment("Dispatching job to crawl `{$siteSearchIndex->crawl_url}`");

                dispatch(new CrawlSiteJob($siteSearchIndex));
            });

        $this->info('All done');
    }
}
