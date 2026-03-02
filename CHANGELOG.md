All notable changes to `laravel-site-search` will be documented in this file.

## 3.0.0 - 2025-02-27

### New Features

- **SQLite Driver**: New driver that uses SQLite FTS5 for full-text search with no external dependencies
  - File-based storage in `storage/site-search/` (configurable)
  - FTS5 full-text search with porter stemming and unicode61 tokenization
  - BM25 ranking algorithm for relevance scoring
  - Atomic index swapping for zero-downtime re-indexing
  - WAL mode for better concurrency
  - Prefix matching support
  - Highlighted search results with `<em>` tags

- **Deep Linking & Anchor Support**: Search results now include anchor links to specific page sections
  - `Hit::urlWithAnchor()` method returns URLs with anchor fragments (e.g., `https://example.com/page#installation`)
  - Automatically extracts heading IDs (`<h1>` through `<h6>` with `id` attributes)
  - Works with all drivers (SQLite, MeiliSearch, ArrayDriver)
  - All drivers deduplicate search results by URL, returning the best match per page

### Breaking Changes

- **Indexer Interface Change**: `Indexer::entries()` now returns an array of arrays with `text` and optional `anchor` keys instead of an array of strings:
  ```php
  // Before: ['text content', 'more text']
  // After: [['text' => 'text content', 'anchor' => 'heading-id'], ['text' => 'more text', 'anchor' => null]]
  ```

- **Driver Interface Change**: Added required `finalizeIndex(string $indexName): self` method to `Driver` interface
  - Called after crawling completes to perform driver-specific finalization
  - SQLite driver uses this for atomic index swapping
  - Other drivers implement as no-op

- **Database Schema Change** (SQLite only): Added `anchor` column to `documents` table
  - Existing SQLite databases will need to be re-indexed to populate anchor data

### Improvements

- **MeiliSearch Driver**: Added support for anchor field in documents
- **Hit Class**: Added `urlWithAnchor()` method for accessing URLs with anchor fragments
- **DefaultIndexer**: Now extracts heading anchors while indexing
- **SearchProfileCrawlObserver**: Updated to handle new entry format with anchors
- **CrawlSiteJob**: Added `finalizeIndex()` call after crawling completes

### Documentation

- Added comprehensive SQLite driver documentation
- Updated installation docs with SQLite setup instructions
- Added deep linking/anchor feature documentation
- Updated requirements docs to mention SQLite has no additional dependencies

**Full Changelog**: https://github.com/spatie/laravel-site-search/compare/2.5.0...3.0.0

## 2.7.0 - 2026-02-22

- Add Laravel 13 support
- Upgrade to Pest 4
- Drop Laravel 10 and PHP 8.2 support

## 3.0.0 - 2026-02-22

- Add Laravel 13 support
- Upgrade to Pest 4
- Drop Laravel 10 support
- Drop PHP 8.2 support (minimum is now PHP 8.3)

## 2.6.1 - 2026-02-22

Use MeiliSearch service container with health check in CI

## 2.6.0 - 2026-02-22

Add Laravel 13 support


## 2.5.0 - 2025-06-20

- Add `ArrayDriver` with logging functionality for debugging the crawling and indexing process

**Full Changelog**: https://github.com/spatie/laravel-site-search/compare/2.4.0...2.5.0

## 2.4.0 - 2025-06-18

- Add `--sync` option to `site-search:crawl`

**Full Changelog**: https://github.com/spatie/laravel-site-search/compare/2.3.1...2.4.0

## 2.3.1 - 2025-02-17

### What's Changed

* Laravel 12.x Compatibility by @laravel-shift in https://github.com/spatie/laravel-site-search/pull/48

**Full Changelog**: https://github.com/spatie/laravel-site-search/compare/2.3.0...2.3.1

## 2.3.0 - 2024-06-17

### What's Changed

* Update links to Meilisearch docs by @timmydhooghe in https://github.com/spatie/laravel-site-search/pull/44
* Use the new reversable prompt form builder and fix CreateSearchConfigCommand Tests by @masterfermin02 in https://github.com/spatie/laravel-site-search/pull/45

### New Contributors

* @masterfermin02 made their first contribution in https://github.com/spatie/laravel-site-search/pull/45

**Full Changelog**: https://github.com/spatie/laravel-site-search/compare/2.2.0...2.3.0

## 2.2.0 - 2024-03-04

### What's Changed

* Laravel 11.x Compatibility by @laravel-shift in https://github.com/spatie/laravel-site-search/pull/39

### New Contributors

* @laravel-shift made their first contribution in https://github.com/spatie/laravel-site-search/pull/39

**Full Changelog**: https://github.com/spatie/laravel-site-search/compare/2.1.2...2.2.0

## 1.5.1 - 2023-08-17

**Full Changelog**: https://github.com/spatie/laravel-site-search/compare/1.5.0...1.5.1

## 2.1.2 - 2023-08-17

### What's Changed

- Use the searchParameters also for paging. by @eggnaube in https://github.com/spatie/laravel-site-search/pull/37
- Also apply the searchParameter - V1 by @eggnaube in https://github.com/spatie/laravel-site-search/pull/38

### New Contributors

- @eggnaube made their first contribution in https://github.com/spatie/laravel-site-search/pull/37

**Full Changelog**: https://github.com/spatie/laravel-site-search/compare/2.1.0...2.1.2

## 2.1.0 - 2023-08-02

- use Laravel Prompts

## 2.0.0 - 2023-06-04

- upgrade to crawler v8

## 1.5.0 - 2023-02-09

- support Meilisearch 1.0

## 1.4.2 - 2023-01-25

- support L10

## 1.4.1 - 2022-11-02

### What's Changed

- Make the CrawlSiteJob unique by @riasvdv in https://github.com/spatie/laravel-site-search/pull/30

### New Contributors

- @riasvdv made their first contribution in https://github.com/spatie/laravel-site-search/pull/30

**Full Changelog**: https://github.com/spatie/laravel-site-search/compare/1.4.0...1.4.1

## 1.4.0 - 2022-10-24

### What's Changed

- Allow to customise job by @freekmurze in https://github.com/spatie/laravel-site-search/pull/29

### New Contributors

- @freekmurze made their first contribution in https://github.com/spatie/laravel-site-search/pull/29

**Full Changelog**: https://github.com/spatie/laravel-site-search/compare/1.3.0...1.4.0

## 1.3.0 - 2022-09-29

- add support for search parameters

## 1.2.1 - 2022-08-24

### What's Changed

- Bug fix indexs meilisearch by @mikzero in https://github.com/spatie/laravel-site-search/pull/28

### New Contributors

- @mikzero made their first contribution in https://github.com/spatie/laravel-site-search/pull/28

**Full Changelog**: https://github.com/spatie/laravel-site-search/compare/1.2.0...1.2.1

## 1.2.0 - 2022-01-19

- support Laravel 9

## 1.1.1 - 2022-01-19

- fix serviceprovider so it doesn't load views

## 1.1.0 - 2021-12-11

## What's Changed

- Introduce possibilty to modify the indexed URLs by @marcreichel in https://github.com/spatie/laravel-site-search/pull/22

## New Contributors

- @marcreichel made their first contribution in https://github.com/spatie/laravel-site-search/pull/22

**Full Changelog**: https://github.com/spatie/laravel-site-search/compare/1.0.2...1.1.0

## 1.0.2 - 2021-11-29

## What's Changed

- Fix highlightedSnippet looking in wrong place by @SimonJulian in https://github.com/spatie/laravel-site-search/pull/20

## New Contributors

- @SimonJulian made their first contribution in https://github.com/spatie/laravel-site-search/pull/20

**Full Changelog**: https://github.com/spatie/laravel-site-search/compare/1.0.1...1.0.2

## 1.0.1 - 2021-10-27

- Fix event constructors by @sebdesign in (#12)

## 1.0.0 - 2021-10-19

- initial release
