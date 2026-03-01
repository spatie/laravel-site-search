---
title: Listing indexes
weight: 7
---

You can list all configured search indexes using this command:

```bash
php artisan site-search:list
```

The `status` column will display one of these values:

- `Waiting on first crawl`: you should execute `site-search:crawl` command to start the crawling your site
- `Crawling`: the package is currently crawling your site
- `Processing`: your site has been crawled, but the search driver is still updating the index (only applies when using Meilisearch)

The `# Indexed URLs` show the number of URLs that were indexed to create the index.
`# URLs Found` shows the total number of URLs discovered during the crawl.
`# Failed` shows the number of URLs that failed to crawl (e.g. due to HTTP errors or timeouts).
`Crawl Status` shows why the crawl ended: `completed`, `crawl_limit_reached`, `time_limit_reached`, or `interrupted`.
`# Documents` shows how many entries there are in the underlying index. It's normal that it is a much higher number than `# Indexed URLs`.

