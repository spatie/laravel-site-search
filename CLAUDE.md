# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Laravel Site Search is a Spatie package that crawls and indexes websites to provide full-text search functionality. It uses SQLite FTS5 as the default search engine (with optional Meilisearch support) and Spatie Crawler for site crawling.

## Common Commands

```bash
# Run tests
composer test

# Run a single test
./vendor/bin/pest --filter "test name"

# Run tests in a specific file
./vendor/bin/pest tests/Feature/IntegrationTest.php

# Start Meilisearch via Docker (required for Meilisearch-specific tests)
docker run -d -p 7700:7700 getmeili/meilisearch:latest ./meilisearch --no-analytics=true
```

## Architecture

### Core Components

**Driver** (`src/Drivers/Driver.php`) - Interface for search engine backends. `SqliteDriver` is the default implementation, `MeiliSearchDriver` is available for advanced use cases, and `ArrayDriver` is for testing.

**SearchProfile** (`src/Profiles/SearchProfile.php`) - Controls crawling behavior: which URLs to crawl, which to index, which indexer to use, and crawler configuration.

**Indexer** (`src/Indexers/Indexer.php`) - Extracts structured data from crawled pages (title, h1, content entries, date modified, extras).

**SiteSearchConfig** (`src/Models/SiteSearchConfig.php`) - Eloquent model storing configuration for each search index (crawl URL, profile class, driver class).

### Data Flow

1. `CrawlSiteJob` is dispatched (via `site-search:crawl` command or manually)
2. Job creates a new temporary index, then uses `SiteSearch` to crawl
3. `SiteSearch` uses Spatie Crawler with `SiteSearchCrawlProfile` and `SearchProfileCrawlObserver`
4. Observer uses the configured `Indexer` to extract page data and the `Driver` to store it
5. After crawling completes, the new index replaces the old one

### Artisan Commands

- `site-search:crawl` - Crawl all enabled sites (use `--sync` to run synchronously)
- `site-search:create` - Create a new SiteSearchConfig via interactive prompts
- `site-search:list` - List all configured search indexes

### Key Configuration Options (config/site-search.php)

- `ignore_content_on_urls` - URLs to crawl but not index
- `ignore_content_by_css_selector` - CSS selectors to exclude from indexing
- `do_not_crawl_urls` - URLs to skip entirely
- `default_profile`, `default_indexer`, `default_driver` - Default implementations

## Testing

Tests use a local test server (`tests/TestSupport/Server/`) with route files that return specific HTML responses. Integration tests run against both SQLite and Meilisearch drivers. Meilisearch tests require a local Meilisearch instance and use `waitForDriver()` to ensure indexing completes.
