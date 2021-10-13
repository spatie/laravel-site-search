<?php

namespace Spatie\SiteSearch\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\SiteSearch\Events\CreatedNewIndexEvent;
use Spatie\SiteSearch\Events\IndexingEndedEvent;
use Spatie\SiteSearch\Events\IndexingStartedEvent;
use Spatie\SiteSearch\Models\SiteSearchIndex;
use Spatie\SiteSearch\SiteSearch;

class CrawlSiteJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use SerializesModels;

    public function __construct(
        public SiteSearchIndex $siteSearchIndex
    ) {
    }

    public function handle()
    {
        event(new IndexingStartedEvent($this->siteSearchIndex));

        $newIndexName = $this->createNewIndex();

        $this->startCrawler();
        $oldIndexName = $this->blessNewIndex($newIndexName);

        if ($oldIndexName) {
            $this->deleteOldIndex($oldIndexName);
        }

        event(new IndexingEndedEvent($this->siteSearchIndex));
    }

    protected function createNewIndex(): string
    {
        $newIndexName = $this->siteSearchIndex->generateAndUpdatePendingIndexName();

        $this->siteSearchIndex->getDriver()->createIndex($newIndexName);

        event(new CreatedNewIndexEvent($newIndexName, $this->siteSearchIndex));

        return $newIndexName;
    }

    protected function startCrawler(): self
    {
        $driver = $this->siteSearchIndex->getDriver();
        $profile = $this->siteSearchIndex->getProfile();

        $siteSearch = new SiteSearch(
            $this->siteSearchIndex->pending_index_name,
            $driver,
            $profile
        );

        $siteSearch->crawl($this->siteSearchIndex->crawl_url);

        return $this;
    }

    protected function blessNewIndex(string $newIndexName): ?string
    {
        $oldIndexName = $this->siteSearchIndex->index_name;

        $this->siteSearchIndex->update(['index_name' => $newIndexName]);

        return $oldIndexName;
    }

    protected function deleteOldIndex(string $oldIndexName): self
    {
        $this->siteSearchIndex->getDriver()->deleteIndex($oldIndexName);

        return $this;
    }
}
