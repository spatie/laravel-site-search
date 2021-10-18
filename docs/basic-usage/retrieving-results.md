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
