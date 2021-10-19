---
title: Creating multiple search indexes
weight: 1
---

This package is able to crawl and create search indexes for multiple sites.

You probably used the `site-search:create` command to [create your first site](/docs/laravel-site-search/v1/basic-usage/indexing-your-first-site). This command creates a record in the `site_search_configs` table.

To crawl and create multiple search indexes, just create multiple rows in that table. When executing `site-search:crawl` an indexing process for each of those sites will be performed.

These are the attributes you should manually update in the table:

- `name`: the name of your index
- `enabled`: if set to `false` , the `site-search:crawl` command will not crawl and update this index
- `crawl_url`: the url to be crawled to populate this index
- `driver_class`: the search driver to use. If this value is `null`, `config('site-search.default_driver')` will be used when indexing the site
- `profile_class`: the search profile to use. If this value is `null`, `config('site-search.default_profile')` will be used when indexing the site 
- `index_base_name`: the name that it used by the underlying search engine (eg. Meilisearch) to determine its index name
- `extra`: used to [customize various settings](/docs/laravel-site-search/v1/advanced-usage/customizing-meilisearch-settings)
- 

These attributes are set by the package, you should not manually set or update them.

- `index_name`: the real name of the underlying index
- `number_of_urls_indexed`: the number of URLs that were crawled to created the index in `index_name`
- `pending_index_name`: will be used to hold the temporary index name that is used while indexing
- `crawling_started_at`: holds the date of when the crawling started when building up this index
- `crawling_ended_at`: holds the date of when the crawling started when building up this index
