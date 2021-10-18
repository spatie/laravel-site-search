---
title: Indexing your first site
weight: 2
---

On this page, you'll learn how to create an index, populate it by crawling your site, and retrieving results. Let's do this!

First, you can run this command to define a site that needs to be indexed.

```php
php artisan site-search:create-index
```

This command will ask for a name for your index, and the URL of your site that should be crawled.

After that you should run this command to start a queued job that crawls your site, and puts the content in a search index:

```php
php artisan site-search:crawl
```

Finally, you can use the `Search` class to perform a query on your index.

```php
use Spatie\SiteSearch\Search;

$searchResults = Search::onIndex($indexName)
    ->query('your query')
    ->get();
```

This is how you could render the results in a Blade view

```html
<ul>
    @foreach($searchResults->hits as $hit)
        <li>
            <a href="{{ $hit->url }}">
                <div>{{ $hit->url }}</div>
                <div>{{ $hit->title() }}</div>
                <div>{!! $hit->highlightedSnippet() !!}</div>
            </a>
        </li>
    @endforeach
</ul>
```



