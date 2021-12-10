<?php

namespace Spatie\SiteSearch\Crawler;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\Crawler\CrawlObservers\CrawlObserver;
use Spatie\SiteSearch\Drivers\Driver;
use Spatie\SiteSearch\Events\FailedToCrawlUrlEvent;
use Spatie\SiteSearch\Events\IndexedUrlEvent;
use Spatie\SiteSearch\Profiles\SearchProfile;

class SearchProfileCrawlObserver extends CrawlObserver
{
    public function __construct(
        protected string $indexName,
        protected SearchProfile $searchProfile,
        protected Driver $driver
    ) {
    }

    public function crawled(
        UriInterface $url,
        ResponseInterface $response,
        ?UriInterface $foundOnUrl = null
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
            ->map(function (string $entry) use ($extra, $dateModified, $url, $h1, $description, $pageTitle) {
                return array_merge([
                    'pageTitle' => $pageTitle,
                    'url' => (string)$url,
                    'h1' => $h1,
                    'entry' => $entry,
                    'description' => $description,
                    'date_modified_timestamp' => $dateModified->getTimestamp(),
                    'id' => (string)Str::uuid(),
                ], $extra);
            })
            ->toArray();

        $this->driver->updateManyDocuments($this->indexName, $documents);

        event(new IndexedUrlEvent($url, $response, $foundOnUrl));
    }

    public function crawlFailed(
        UriInterface $url,
        RequestException $requestException,
        ?UriInterface $foundOnUrl = null
    ): void {
        event(new FailedToCrawlUrlEvent($url, $requestException, $foundOnUrl));
    }
}
