---
title: Installation and setup
weight: 4
---

You can install this package inside a Laravel app of which the content needs to be indexed. You could also install this package inside a standalone Laravel app that is dedicated to crawling multiple other sites.

Here are the steps that you need to perform to install the package.

## Require via composer

laravel-site-search can be installed via Composer:

```bash
composer require spatie/laravel-site-search
```

## Publish migrations

Next, you should publish the migrations and run them:

```bash
php artisan vendor:publish --tag="site-search-migrations"
php artisan migrate
```

## Schedule the crawl command

This package contains a command that will crawl your site(s), and update the indexes.
In most cases, it's best to schedule that command, so you don't need to run it manually.

In the example below, we schedule to run the command every three hours, but you can decide which frequency is best for you.

```php
// in routes/console.php
use Illuminate\Support\Facades\Schedule;
use Spatie\SiteSearch\Commands\CrawlCommand;

Schedule::command(CrawlCommand::class)->everyThreeHours();
```

## Publish the config file

Optionally, you can publish the config file with this command.

```bash
php artisan vendor:publish --tag="site-search-config"
```

This is the content of the config file:

```php
<?php

return [
    /*
     * When crawling your site, we will not add any content to the search index
     * that is on these URLs.
     *
     * All links on these URLs will still be followed and crawled.
     *
     * You may use `*` as a wildcard.
     */
    'ignore_content_on_urls' => [
        //
    ],

    /*
     * When indexing your site, we will not add any content to the search index
     * that is selected by these CSS selectors.
     *
     * All links inside such content will still be crawled, so it's safe
     * to add a selector for your menu structure.
     */
    'ignore_content_by_css_selector' => [
        '[data-no-index]',
        'nav',
    ],

    /*
     * When crawling your site, we will not add any content to the search index
     * for responses that have any of these headers.
     */
    'do_not_index_content_headers' => [
        'site-search-do-not-index',
    ],

    /*
     * When crawling your site, we will not follow any of these links.
     *
     * You may use `*` as a wildcard.
     */
    'do_not_crawl_urls' => [
        //
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
     *
     * Available drivers:
     * - MeiliSearchDriver: uses Meilisearch as the search engine (requires a running Meilisearch instance)
     * - SqliteDriver: uses a local SQLite database with FTS5 full-text search (no external dependencies)
     * - ArrayDriver: in-memory driver for testing
     */
    'default_driver' =>  Spatie\SiteSearch\Drivers\SqliteDriver::class,

    /*
     * This job is responsible for crawling your site. To customize this job,
     * you can extend the default one, and specify the class name of
     * your customized job here.
     */
    'crawl_site_job' => Spatie\SiteSearch\Jobs\CrawlSiteJob::class,
];
```

## Search driver

The default driver is SQLite, which uses SQLite FTS5 for full-text search. It requires no external services. The SQLite databases will be stored in `storage/site-search` by default. See [Using the SQLite driver](/docs/laravel-site-search/v1/advanced-usage/using-the-sqlite-driver) for more configuration options.

If you need advanced features like synonyms and custom ranking rules, you can [use the Meilisearch driver](/docs/laravel-site-search/v1/advanced-usage/using-the-meilisearch-driver) instead.
