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

## FailedToCrawlUrlEvent

Will be fired whenever crawling a page resulted in an error.

It has these properties:

- `url`: an instance of `UriInterface` containing the url that was crawled
- `requestException`: the exception itself
- `foundOnUrl`: an instance of `UriInterface` containing  the url on which the crawled url was found

