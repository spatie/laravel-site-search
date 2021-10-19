---
title: Listing indexes
weight: 7
---

You can list all configured search indexes using this command:

```php
php artisan site-search:index
```

The `status` column will display one of these values:

- `Waiting on first crawl`: you should execute `site-search:crawl` command to start the crawling your site
- `Crawling`: the package is currently crawling your site
- `Processing`: your site has been crawled, but Meilisearch is still updating the index

The `# Indexed URLs` show the number of URLs that were indexed to create the index.
`# Documents` shows how many entries there are in the underlying indexed. It's normal that it is a much higher number than `#Indexed URLs`

