<?php

namespace Spatie\SiteSearch\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;
use Spatie\SiteSearch\Events\IndexedUrlEvent;
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

    protected $numberOfUrlsIndexed = 0;

    public function __construct(
        public SiteSearchConfig $siteSearchConfig
    ) {
    }

    public function handle()
    {
        event(new IndexingStartedEvent($this->siteSearchConfig));

        $this->deleteOldIndexes();

        $newIndexName = $this->createNewIndex();

        $this->startCrawler();

        $oldIndexName = $this->blessNewIndex($newIndexName);

        if ($oldIndexName) {
            $this->deleteOldIndex($oldIndexName);
        }

        event(new IndexingEndedEvent($this->siteSearchConfig));
    }

    protected function deleteOldIndexes(): self
    {
        $driver = $this->siteSearchConfig->getDriver();

        collect($driver->allIndexNames())
            ->filter(fn (string $indexName) => str_starts_with($indexName, $this->siteSearchConfig->index_base_name . '-'))
            ->reject(function (string $indexName) {
                return in_array($indexName, [
                    $this->siteSearchConfig->index_name,
                    $this->siteSearchConfig->pending_index_name,
                ]);
            })
            ->each(fn (string $indexName) => $driver->deleteIndex($indexName));

        return $this;
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
        Event::listen(function (IndexedUrlEvent $event) {
            $this->numberOfUrlsIndexed = $this->numberOfUrlsIndexed + 1;
        });

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
            'number_of_urls_indexed' => $this->numberOfUrlsIndexed,
        ]);

        $this->siteSearchConfig->document_count;

        return $oldIndexName;
    }

    protected function deleteOldIndex(string $oldIndexName): self
    {
        $this->siteSearchConfig->getDriver()->deleteIndex($oldIndexName);

        return $this;
    }
}
