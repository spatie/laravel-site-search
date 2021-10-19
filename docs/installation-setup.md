---
title: Installation and setup
weight: 4
---

You can install this package inside a Laravel app of which the content needs to be indexed. You could also install this package inside a standalone Laravel app that is dedicated to crawling multiple other sites.

Here are the steps that you need to perform to install the package.

## Require via composer

laravel-site-search can be installed via Composer:

```bash
composer require "spatie/laravel-site-search:^1.0.0"
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
// in app/Console/Kernel.php
use Spatie\SiteSearch\Commands\CrawlCommand;

protected function schedule(Schedule $schedule)
{
    // other commands
    // ...
    
    $schedule->command(CrawlCommand::class)->everyThreeHours();
}
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
     * it's safe to add a selector for your menu structure.
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
     */
    'default_driver' =>  Spatie\SiteSearch\Drivers\MeiliSearchDriver::class,
];
```

## Install the Meilisearch client

Next, you should require the Meilisearch PHP client:

```bash
composer require meilisearch/meilisearch-php
```

## Install Meilisearch

This package uses Meilisearch under the hood to provide blazing fast search results.
Head over to [the Meilisearch docs](https://docs.meilisearch.com/learn/getting_started/installation.html#download-and-launch) to learn how to install it on your system. 

Here are the steps for installing it on a Forge provisioned server. You must first download the stable release:

```bash
curl -L https://install.meilisearch.com | sh
```

Next, you must change the ownership and modify permission:

```bash
chmod 755 meilisearch 
chown root:root meilisearch
```

After that, move the binary to a system-wide available path:

```bash
sudo mv meilisearch /usr/bin/
```

Finally, you can run the binary and make sure it keeps running. In the Forge Dashboard, click on "Daemons" under "Server Details". Fill out the following for a new daemon:

- Command: `meilisearch --master-key=SOME_MASTER_KEY --env=production --http-addr 0.0.0.0:7700 --db-path ./home/forge/meilifiles`
- User: `forge` 
- Directory: leave blank 
- Processes: `1`

These instructions were take from [this gist](https://gist.github.com/josecanhelp/126d627ef125538943f33253d16fc882) by Jose Soto.

## Authenticating requests to Meilisearch

To avoid unauthorized persons making request to Meilisearch, either block Meilisearch's default port (7700) in your firewall, or make sure all requests [use authentication](/docs/laravel-site-search/v1/basic-usage/authenticating-requests).
