---
title: Using a custom indexer
weight: 2
---

When a site gets crawled, each of the pages is fed to a [search profile](/docs/laravel-site-search/v1/basic-usage/using-a-search-profile). When that search profile determines that page should be indexed, the URL and response for that page is being given to an indexer. The job of the indexer is to extract the title of the page, the h1, description, content, ... that should be put in the site index.

By default, the `Spatie\SiteSearch\Indexers\DefaultIndexer` is used. This indexer makes the best effort in determining the page title, description, and content of your page.

The implementation of `entries()` of the `DefaultIndexer` will extract text content from your page. The database driver consolidates all entries for a URL into a single row, so each page results in one search record.

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
     * This function should return the content of the response chopped up in
     * little pieces of text.
     *
     * Each entry should be an array with 'text' and optionally 'anchor' keys:
     * [
     *     ['text' => 'Introduction text...', 'anchor' => 'intro'],
     *     ['text' => 'More content...', 'anchor' => null],
     * ]
     *
     * The database driver will consolidate all entries for a URL into
     * a single row with concatenated text.
     */
    public function entries(): array;

    /*
     * This function should return the date when the content
     * was modified for the last time.
     */
    public function dateModified(): ?CarbonInterface;

    /*
     * The meta description that should be put in the search index.
     */
    public function description(): ?string;

    /*
     * Any keys and values this function returns will also
     * be put in the search index. This is useful for adding
     * custom attributes.
     *
     * More info: https://spatie.be/docs/laravel-site-search/v1/advanced-usage/indexing-extra-properties
     */
    public function extra(): array;

    /*
     * This function should return the url of the page.
     */
    public function url(): string;
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

Here's an example of a custom indexer [used at freek.dev](https://github.com/spatie/freek.dev/blob/3fdfc1ecc958be75563a3b54a72194c3a0c3e1ca/app/Services/Search/Indexer.php) that will remove the suffix of site.

```php

namespace App\Services\Search;

use Spatie\SiteSearch\Indexers\DefaultIndexer;

class Indexer extends DefaultIndexer
{
    public function pageTitle(): ?string
    {
        return str_replace(
            " - Freek Van der Herten's blog on PHP, Laravel and JavaScript",
            '',
            parent::pageTitle()
        );
    }
}
```

Here's an example of a custom indexer to strip away the query parameters from the url.

```php

namespace App\Services\Search;

use Spatie\SiteSearch\Indexers\DefaultIndexer;

class Indexer extends DefaultIndexer
{
    public function url(): string
    {
        $parsed = parse_url($this->url);

        return ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? '') . ($parsed['path'] ?? '/');
    }
}
```

Here's an example of a custom indexer to use the canonical url (if applicable) as the url.

```php

namespace App\Services\Search;

use Spatie\SiteSearch\Indexers\DefaultIndexer;

class Indexer extends DefaultIndexer
{
    public function url(): string
    {
        $canonical = attempt(fn () => $this->domCrawler->filter('link[rel="canonical"]')->first()->attr('href'));

        if (! $canonical) {
            return parent::url();
        }

        return $canonical;
    }
}
```
