---
title: Creating multiple search indexes
weight: 1
---

This package is able to crawl and create search indexes for multiple sites.

You probably used the `search-index:create` command to [create your first site](TODO: add url). This command creates a record in the `site_search_indices` table.

To crawl and create multiple search indexes, just create multiple rows in that table. When executing `search-index:crawl` an indexing process for each of those sites will be performed.

These are the attributes you should manually update in the table:

- `name`: //TODO url
- `enabled`: if set to `false` , the `site-search:crawl` command will not crawl and update this index
- `crawl_url`: the url to be crawled to populate this index
- `driver_class`: the search driver to use. If this value is `null`, `config('site-serach.default_driver')` will be used when indexing the site
- `profile_class`: the search profile to use. If this value is `null`, `config('site-serach.default_profile')` will be used when indexing the site 
- `index_base_name`: the name that it used by the underlying search engine (eg. Meilisearch) to determine its index name

These attributes are set by the package, you should not manually set or update them.

- `pending_index_name`: will be used to hold the temporary index name that is used while indexing
- `crawling_started_at`: holds the date of when the crawling started when building up this index
- `crawling_ended_at`: holds the date of when the crawling started when building up this index

- `extra`: reserved for future use
