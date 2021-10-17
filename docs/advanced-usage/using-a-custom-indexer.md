---
title: Using a custom indexer
weight: 2
---

When a site gets crawled, each of the pages is fed to a [search profile](/docs/laravel-site-search/v1/basic-usage/using-a-search-profile). When that search profile determines that page should be indexed, the URL and response for that page is being given to an indexer. The job of the indexer is to extract the title of the page, the h1, description, content, ... that should be put in the site index.

By default, the `Spatie\SiteSearch\Indexers\DefaultIndexer`... is used. This indexer makes the best effort in determining the page title, description, and content of your page.

If the results yielded by `DefaultIndexer` are not good enough for your content, you can create a custom indexer. An indexer is any class that implements `Spatie\SiteSearch\Indexers\Indexer`. Here's how that interface looks like.

```php
namespace Spatie\SiteSearch\Indexers;

use Carbon\CarbonInterface;

interface Indexer
{
    /*
     * The page title that should be put in the search index.
     */
    public function pageTitle(): ?string;

    /*
     * The H1 that should be put in the search index.
     */
    public function h1(): ?string;

    /*
     * We can index all html of the page directly, as most search engines have
     * a small limit on how long a search entry should be.
     *
     * This function should return an array the content of the response chopped up in
     * little pieces of text of a few sentences long.
     */
    public function entries(): array;

    /*
     * This function should return the date when the content
     * was modified for the last time.
     */
    public function dateModified(): ?CarbonInterface;

    /*
     * Any keys and values this function returns will also
     * be put in the search index.
     */
    public function extra(): array;
}
```

In most cases, it's probably the easiest to extend the `DefaultIndexer`

```php
class YourIndexer extends Spatie\SiteSearch\Indexers\DefaultIndexer
{
    // override the desired method
}
```

To use your custom indexer, specify its class name in the `default_indexer` key of the  `site-search` config file.
