---
title: Retrieving results
weight: 3
---

You can retrieve results from an index using `Spatie\SiteSearch\Search`.

## Getting all results

Here's how you can retrieve all results from an index named `my-index`.

```php
use Spatie\SiteSearch\Search;

$searchResults = Search::onIndex('my-index')
    ->query('your query')
    ->get(); // returns all results
```

## Limiting results

You can limit the amount of results using the `limit` function.

```php
use Spatie\SiteSearch\Search;

$searchResults = Search::onIndex('my-index')
    ->query('your query')
    ->limit(20)
    ->get(); // returns the first 20 results
```

## Paginating results

You can paginate results using by calling `paginate`.

```php
use Spatie\SiteSearch\Search;

$searchResults = Search::onIndex('my-index')
    ->query('your query')
    ->paginate(20); // returns an instance of `Illuminate\Pagination\Paginator` with 20 results per page
```

## Deep linking to sections

Search results can link directly to a specific section of a page. When your HTML headings have `id` attributes, the indexer automatically extracts them and associates them with nearby text content.

```html
<h2 id="installation">Installation</h2>
<p>To install the package, run...</p>

<h2 id="configuration">Configuration</h2>
<p>After installation, configure...</p>
```

Use the `urlWithAnchor()` method on a `Hit` to get the full URL including the fragment:

```php
foreach ($searchResults->hits as $hit) {
    $url = $hit->urlWithAnchor(); // https://example.com/page#configuration
}
```

If no anchor is available for the matched content, `urlWithAnchor()` returns the base URL.

This works with all drivers (SQLite, Meilisearch, etc.).
