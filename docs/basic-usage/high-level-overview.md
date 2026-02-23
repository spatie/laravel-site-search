---
title: High level overview
weight: 1
---

This package will crawl your entire site and will put the content in a search index. This way, the entire content of your site is searchable. Think of it as a private Google search index.

The package supports two search drivers: **Meilisearch** and **SQLite**. The search and indexing API is the same regardless of which driver you use.

The configuration for each site that needs to be crawled is saved in the `site_search_configs` table. You can manually create a row in that table or run this artisan command: [`site-search:create-index`](https://spatie.be/docs/laravel-site-search/v1/basic-usage/indexing-your-first-site).

Next, you can fill up a search index by executing [the crawl command](https://spatie.be/docs/laravel-site-search/v1/basic-usage/indexing-your-first-site). Before that command actually starts crawling, it will clean up old indexes whose names start with the `index_base_name` specified in the `site_search_configs` table. After that, it will create a new empty index. The name of that new index will be saved in the `pending_index_name` column of the `site_search_configs` table.

[A search profile class](/docs/laravel-site-search/v1/basic-usage/using-a-search-profile) will determine which pages get crawled and which pages should be put in the search index. [An indexer class](/docs/laravel-site-search/v1/advanced-usage/using-a-custom-indexer) will transform the HTML of a page to something that can be saved in the index.

When crawling your site, multiple concurrent connections are used to speed up the crawling process.

After the site has been crawled, the old index will be deleted and replaced by the newly built one.

