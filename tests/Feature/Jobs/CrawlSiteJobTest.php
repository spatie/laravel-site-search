<?php

use Spatie\SiteSearch\Jobs\CrawlSiteJob;
use Spatie\SiteSearch\Models\SiteSearchIndex;
use Spatie\SiteSearch\SiteSearch;
use Tests\Server\Server;

it('can crawl a site', function () {
    Server::boot();

    Server::activateRoutes('homePage');

    $siteSearchIndex = SiteSearchIndex::create([
        'name' => 'test',
        'index_base_name' => 'test',
        'crawl_url' => 'http://localhost:8181',
    ]);

    dispatch(new CrawlSiteJob($siteSearchIndex));

    sleep(6);

    /** @var \Spatie\SiteSearch\SearchResults\SearchResults $searchResults */
    $searchResults = SiteSearch::index('test')->query('content');

    expect($searchResults->hits)->toHaveCount(1);

    /** @var \Spatie\SiteSearch\SearchResults\Hit $hit */
    $hit = $searchResults->hits[0];

    expect($hit)
        ->pageTitle->toEqual('My title')
        ->entry->toEqual('My content');
})->only();
