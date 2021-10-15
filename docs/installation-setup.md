---
title: Base installation
weight: 4
---

laravel-site-search can be installed via Composer:

```bash
composer require "spatie/laravel-site-search:^1.0.0"
```

Next, you should publish the migrations


Optionally, You can publish the config file with this command.

```bash
php artisan vendor:publish --tag="site-search-config"
```

This is the content of the config file:

```php
return [
    /*
     * When indexing your site, we will not add any content to the search index
     * that is selected by these CSS selectors.
     *
     * All links inside such content will still be crawled, so it's safe
     * it's safe to add a selector for your menu structure.
     */
    'do_not_index' => [
        '[data-no-index]',
        'nav',
    ],

    /*
     * A search profile is a class that is responsible for determining which
     * pages should be crawled, whether they should be indexed, and which
     * indexer should be used.
     *
     * This profile will be used when none is specified in the `profile_class` attribute
     * of a `SiteSearchIndex` model.
     */
    'default_profile' => Spatie\SiteSearch\Profiles\DefaultSearchProfile::class,

    /*
     * An indexer is a class that is responsible for converting the content of a page
     * to a structure that will be added to the search index.
     *
     * This indexer will be used when none is specified in the `profile_class` attribute
     * of a `SiteSearchIndex` model.
     */
    'default_indexer' => Spatie\SiteSearch\Indexers\DefaultIndexer::class,

    /*
     * A driver is responsible for writing all scraped content
     * to a search index.
     */
    'default_driver' =>  Spatie\SiteSearch\Drivers\MeiliSearchDriver::class,
];
```
