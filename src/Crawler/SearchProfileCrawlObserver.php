<?php

namespace Spatie\SiteSearch\Crawler;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Str;
use Spatie\Crawler\CrawlObservers\CrawlObserver;
use Spatie\Crawler\CrawlProgress;
use Spatie\Crawler\CrawlResponse;
use Spatie\Crawler\Enums\FinishReason;
use Spatie\Crawler\Enums\ResourceType;
use Spatie\SiteSearch\Drivers\Driver;
use Spatie\SiteSearch\Events\CrawlFinishedEvent;
use Spatie\SiteSearch\Events\FailedToCrawlUrlEvent;
use Spatie\SiteSearch\Events\IndexedUrlEvent;
use Spatie\SiteSearch\Profiles\SearchProfile;

class SearchProfileCrawlObserver extends CrawlObserver
{
    public function __construct(
        protected string $indexName,
        protected SearchProfile $searchProfile,
        protected Driver $driver
    ) {}

    public function crawled(
        string $url,
        CrawlResponse $response,
        CrawlProgress $progress,
    ): void {
        if (! $this->searchProfile->shouldIndex($url, $response)) {
            return;
        }

        $indexer = $this->searchProfile->useIndexer($url, $response);

        if (! $indexer) {
            return;
        }

        $pageTitle = $indexer->pageTitle();
        $h1 = $indexer->h1();
        $dateModified = $indexer->dateModified();
        $description = $indexer->description();
        $extra = $indexer->extra();
        $url = $indexer->url();

        $documents = collect($indexer->entries())
            ->map(function (array $entry) use ($extra, $dateModified, $url, $h1, $description, $pageTitle) {
                return array_merge([
                    'pageTitle' => $pageTitle,
                    'url' => $url,
                    'h1' => $h1,
                    'entry' => $entry['text'],
                    'anchor' => $entry['anchor'] ?? null,
                    'description' => $description,
                    'date_modified_timestamp' => $dateModified?->getTimestamp(),
                    'id' => (string) Str::uuid(),
                ], $extra);
            })
            ->toArray();

        $this->driver->updateManyDocuments($this->indexName, $documents);

        event(new IndexedUrlEvent($url, $response, $progress, $response->foundOnUrl()));
    }

    public function crawlFailed(
        string $url,
        RequestException $requestException,
        CrawlProgress $progress,
        ?string $foundOnUrl = null,
        ?string $linkText = null,
        ?ResourceType $resourceType = null,
    ): void {
        event(new FailedToCrawlUrlEvent($url, $requestException, $progress, $foundOnUrl));
    }

    public function finishedCrawling(FinishReason $reason, CrawlProgress $progress): void
    {
        event(new CrawlFinishedEvent($reason, $progress));
    }
}
