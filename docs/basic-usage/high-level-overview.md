---
title: High level overview
weight: 1
---

This package will crawl your entire site and will put the content in a search index. This way the entire contents of your site is searchable. Think of it as a private Google search index.

When crawling your site, multiple concurrent connections are used to speed up the crawling process. Each page that is crawled will be transformed by an `Indexer`. 

This class is responsible for determining what the title is of a page, what the description is, 

