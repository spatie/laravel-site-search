---
title: Using a search profile
weight: 5
---

A search profile determines which pages get crawled and what content gets indexed. In the `site-search` config file, you'll in the `default_profile` key that the `Spatie\SiteSearch\Profiles\DefaultSearchProfile::class` is being use by default.

This default profile will instruct the indexing process:
- to crawl each page of your site
- to only index any page that had `200` as the status code of its response
- to not index a page if the response had a header `site-search-do-not-index`

By default, the crawling process will respect the `robots.txt` of your site.

A search profile is also responsible for determining which indexer will be used for a certain page. An indexer is responsible for determining the title, content, description, ... of a page. By default, `Spatie\SiteSearch\Indexers\DefaultIndexer` will get used. To know more about indexers and how to customize them, head over to [the section on indexers](/docs/laravel-site-search/v1/advanced-usage/using-a-custom-indexer).

## Creating your own search profile

If you want to customize the crawling and indexing behavior, you could opt to extend `Spatie\SiteSearch\Profiles\DefaultSearchProfile` or create your own class that implements the `Spatie\SiteSearch\Profiles\SearchProfile` interface. This is how that interface looks like.

```php
namespace Spatie\SiteSearch\Profiles;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\SiteSearch\Indexers\Indexer;

interface SearchProfile
{
    public function shouldCrawl(UriInterface $url, ResponseInterface $response): bool;
    public function shouldIndex(UriInterface $url, ResponseInterface $response): bool;
    public function useIndexer(UriInterface $url, ResponseInterface $response): ?Indexer;
    public function configureCrawler(Crawler $crawler): void;
}
```







