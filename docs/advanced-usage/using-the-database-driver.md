---
title: Using the database driver
weight: 5
---

The database driver is the default driver. It stores search documents in your application's database and uses the database's native full-text search capabilities. SQLite (FTS5), MySQL (FULLTEXT), and PostgreSQL (tsvector) are all supported.

## How it works

The database driver stores all indexed documents in a single `site_search_documents` table. Each document is associated with an `index_name`, which allows multiple search indexes to coexist in the same table.

Full-text search infrastructure (virtual tables, indexes, triggers) is created automatically at runtime based on which database you are using. The migration itself is database-agnostic.

### Database-specific behavior

**SQLite** uses FTS5 virtual tables with porter stemming and unicode support. BM25 ranking weights matches in headings higher than body content. Highlighted snippets are generated natively by FTS5.

**MySQL** uses FULLTEXT indexes with boolean mode search. Highlighting is done in PHP after the query.

**PostgreSQL** uses tsvector columns with GIN indexes and weighted vectors. `ts_rank()` is used for ranking and `ts_headline()` for highlighting.

### Search features

All three databases provide:

- Full-text search with stemming (e.g. "running" matches "run")
- Prefix matching (e.g. "auth" matches "authentication")
- Relevance ranking with field-specific weights
- Highlighted snippets with matching terms wrapped in `<em>` tags
- Deep linking with anchor links to specific sections (see [Retrieving results](/docs/laravel-site-search/v1/basic-usage/retrieving-results#deep-linking-to-sections))

## Using a different database connection

By default, the database driver uses your application's default database connection. You can use a different connection by setting a `database.connection` value in the `extra` attribute of the `site_search_configs` table:

```json
{"database": {"connection": "mysql"}}
```

This lets you store search data in a separate database from your application data.

## Differences from the Meilisearch driver

- No external service required, uses your existing database
- No support for synonyms or custom ranking rules
- Indexing is synchronous (no background processing)
