<?php

use Illuminate\Support\Facades\Bus;
use function Pest\Laravel\artisan;
use Spatie\SiteSearch\Commands\CrawlSitesCommand;
use Spatie\SiteSearch\Jobs\CrawlSiteJob;
use Spatie\SiteSearch\Models\SiteSearchIndex;
use Symfony\Component\Console\Command\Command;

beforeEach(function () {
    Bus::fake();
});

it('will crawl sites for enabled site indexes', function () {
    SiteSearchIndex::factory()->create();

    artisan(CrawlSitesCommand::class)->assertExitCode(Command::SUCCESS);

    Bus::assertDispatched(CrawlSiteJob::class);
});

it('will not crawl sites for disabled site indexes', function () {
    SiteSearchIndex::factory()->create(['enabled' => false]);

    artisan(CrawlSitesCommand::class)->assertExitCode(Command::SUCCESS);

    Bus::assertNotDispatched(CrawlSiteJob::class);
});
