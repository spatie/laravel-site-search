---
title: Indexing extra properties
weight: 3
---

By default, only the page title, URL, description, and some content are added to the search index. However, you can add any extra property you want.

You do this by using [a custom indexer](/docs/laravel-site-search/v1/advanced-usage/using-a-custom-indexer) and override the `extra` method.

```php
class YourIndexer extends Spatie\SiteSearch\Indexers\DefaultIndexer
{
    public function extra() : array{
        return [
            'authorName' => $this->functionThatExtractsAuthorName()
        ]    
    }
    
    public function functionThatExtractsAuthorName()
    {
        // add logic here to extract the username using
        // the `$response` property that's set on this class
    }
}
```

The extra properties will be available on a search result hit.

```php
$searchResults = SearchIndexQuery::onIndex('my-index')->search('your query')->get(); 

$firstHit = $searchResults->hits->first();

$firstHit->authorName; // returns the author name
```

All extra properties are searchable by default. If you don't want any of your extra attributes to be searchable, you must [customize the search index settings](/docs/laravel-site-search/v1/advanced-usage/customizing-meilisearch-settings).
