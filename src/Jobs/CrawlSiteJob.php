<?php

namespace Spatie\SiteSearch\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\SiteSearch\Events\IndexingEndedEvent;
use Spatie\SiteSearch\Events\IndexingStartedEvent;
use Spatie\SiteSearch\Events\NewIndexCreatedEvent;
use Spatie\SiteSearch\Models\SiteSearchConfig;
use Spatie\SiteSearch\SiteSearch;

class CrawlSiteJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use SerializesModels;

    public function __construct(
        public SiteSearchConfig $siteSearchConfig
    ) {
    }

    public function handle()
    {
        event(new IndexingStartedEvent($this->siteSearchConfig));

        $newIndexName = $this->createNewIndex();

        $this->startCrawler();

        $oldIndexName = $this->blessNewIndex($newIndexName);

        if ($oldIndexName) {
            $this->deleteOldIndex($oldIndexName);
        }

        event(new IndexingEndedEvent($this->siteSearchConfig));
    }

    protected function createNewIndex(): string
    {
        $newIndexName = $this->siteSearchConfig->generateAndUpdatePendingIndexName();

        $this->siteSearchConfig->getDriver()->createIndex($newIndexName);

        event(new NewIndexCreatedEvent($newIndexName, $this->siteSearchConfig));

        return $newIndexName;
    }

    protected function startCrawler(): self
    {
        $driver = $this->siteSearchConfig->getDriver();
        $profile = $this->siteSearchConfig->getProfile();

        $siteSearch = new SiteSearch(
            $this->siteSearchConfig->pending_index_name,
            $driver,
            $profile
        );

        $this->siteSearchConfig->update(['crawling_started_at' => now()]);
        $siteSearch->crawl($this->siteSearchConfig->crawl_url);
        $this->siteSearchConfig->update(['crawling_ended_at' => now()]);

        return $this;
    }

    protected function blessNewIndex(string $newIndexName): ?string
    {
        $oldIndexName = $this->siteSearchConfig->index_name;

        $this->siteSearchConfig->update([
            'index_name' => $newIndexName,
            'pending_index_name' => null,
        ]);

        return $oldIndexName;
    }

    protected function deleteOldIndex(string $oldIndexName): self
    {
        $this->siteSearchConfig->getDriver()->deleteIndex($oldIndexName);

        return $this;
    }
}
