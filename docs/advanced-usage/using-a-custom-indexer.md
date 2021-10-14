---
title: Creating multiple search indexes
weight: 2
---

When a site gets crawled, each of the pages is fed to a [search profile](TODO: add URL). When that search profile determines that page should be indexed, the URL and response is being given to an indexer. The job of the indexer is to extract the title of the page, the h1, description, content, ... that should be put in the site index.

By default, the `Spatie\SiteSearch\Indexers\DefaultIndexer::class`... TODO

An indexer is any class that implements `Spatie\SiteSearch\Indexers\Indexer`. Here's how that interface looks like.

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
     * We can index all html of page directly, as most search engines have
     * a small limit on how long a search entry should be.
     *
     * This function should return the content of the response chopped up in
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


