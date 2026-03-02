---
title: Available events
weight: 5
---

You can listen for these events to add extra functionality.

## IndexingStartEvent

This event will be fired when the indexing process starts, right before a search index is created.

It has these properties:

- `siteSearchConfig`: an instance of `SiteSearchConfig` for which the indexing process is starting

## IndexingEndedEvent

This event will be fired when the indexing process has ended.

It has these properties:

- `siteSearchConfig`: an instance of `SiteSearchConfig` for which the indexing process is starting

## NewIndexCreatedEvent

Right before we start crawling a site, a new search index will be created, firing off this event.

It has these properties:

- `newIndexName` the name of the new search index
- `siteSearchConfig`: an instance of `SiteSearchConfig` for which the new event has been created

## IndexedUrlEvent

Will be fired whenever a URL has been successfully crawled and indexed.

It has these properties:

- `url`: a `string` containing the url that was indexed
- `response`: an instance of `CrawlResponse`
- `progress`: an instance of `CrawlProgress` with properties `urlsCrawled`, `urlsFailed`, `urlsFound`, and `urlsPending`
- `foundOnUrl`: an optional `string` containing the url on which the indexed url was found

## FailedToCrawlUrlEvent

Will be fired whenever crawling a page resulted in an error.

It has these properties:

- `url`: a `string` containing the url that was crawled
- `requestException`: the exception itself
- `progress`: an instance of `CrawlProgress` with properties `urlsCrawled`, `urlsFailed`, `urlsFound`, and `urlsPending`
- `foundOnUrl`: an optional `string` containing the url on which the crawled url was found

## CrawlFinishedEvent

Will be fired when the crawler has finished crawling a site.

It has these properties:

- `finishReason`: an instance of `FinishReason` (an enum with values `Completed`, `CrawlLimitReached`, `TimeLimitReached`, and `Interrupted`)
- `progress`: an instance of `CrawlProgress` with properties `urlsCrawled`, `urlsFailed`, `urlsFound`, and `urlsPending`

The `finishReason` and progress counters (`urls_found`, `urls_failed`) from this event are automatically persisted to the `site_search_configs` table after each crawl.
