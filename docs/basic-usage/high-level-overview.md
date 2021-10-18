---
title: High level overview
weight: 1
---

This package will crawl your entire site and will put the content in a search index. This way, the entire content of your site is searchable. Think of it as a private Google search index.

The configuration for each site that needs to be crawled is saved in the `site_search_configs` table. You can manually create a row in that table or run this artisan command: [`site-search:create-index`](https://spatie.be/docs/laravel-site-search/v1/basic-usage/indexing-your-first-site).

Next, you can fill up a search index by executing [the crawl command](https://spatie.be/docs/laravel-site-search/v1/basic-usage/indexing-your-first-site). Before that commands actually start crawling, it will clean up old indexes whose names start with the `index_base_name` specified in the `site_search_configs` table. After that, it will create a new empty Meilisearch index. The name of that new index will be saved in the `pending_index_name` column of the `site_search_configs` table.

[A search profile class](/docs/laravel-site-search/v1/basic-usage/using-a-search-profile) will determine which pages get crawled and which pages should be put in the Meilisearch index. [An indexer class](/docs/laravel-site-search/v1/advanced-usage/using-a-custom-indexer) will transform the HTML of a page to something that can be saved in the index.

When crawling your site, multiple concurrent connections are used to speed up the crawling process.

After the site has been crawled, the Meilisearch index whose name is in `index_name` of `site_search_configs` will be deleted. The `index_name` will now be set to the value of `pending_index_name`.

