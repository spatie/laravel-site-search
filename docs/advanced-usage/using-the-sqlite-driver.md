---
title: Using the SQLite driver
weight: 5
---

The SQLite driver is the default driver. It uses SQLite's FTS5 (Full-Text Search 5) engine to provide full-text search with no external dependencies.

## Customizing the storage path

By default, SQLite databases are stored in `storage/site-search`. You can customize this per index by setting a `sqlite.storage_path` value in the `extra` attribute of the `site_search_configs` table:

```json
{"sqlite": {"storage_path": "/custom/path/to/storage"}}
```

## How it works

The SQLite driver creates a separate `.sqlite` database file for each search index. Each database contains:

- A `documents` table for storing page content
- An FTS5 virtual table for full-text search with porter stemming and unicode support
- Triggers that keep the FTS index synchronized with the documents table

### Atomic index swaps

When a crawl is in progress, the new index is built in a temporary `.sqlite.tmp` file. Once the crawl completes, the temporary file atomically replaces the existing database. This ensures zero downtime during re-indexing.

### Search features

- Full-text search with porter stemming (e.g. "running" matches "run")
- Prefix matching (e.g. "auth" matches "authentication")
- BM25 ranking with field-specific weights, matches in headings are ranked higher than matches in body content
- Highlighted snippets with matching terms wrapped in `<em>` tags
- Deep linking, search results include anchor links to specific sections (see [Retrieving results](/docs/laravel-site-search/v1/basic-usage/retrieving-results#deep-linking-to-sections))

## Differences from the Meilisearch driver

- No external service required, everything is file-based
- No support for synonyms or custom ranking rules
- Indexing is synchronous (no background processing)
