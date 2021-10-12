<?php

use Spatie\SiteSearch\Jobs\CrawlSiteJob;
use Spatie\SiteSearch\Models\SiteSearchIndex;
use Spatie\SiteSearch\SiteSearch;
use Tests\Server\Server;
use Tests\TestClasses\SearchProfiles\DoNotCrawlSecondLinkSearchProfile;
use Tests\TestClasses\SearchProfiles\DoNotIndexSecondLinkSearchProfile;

beforeEach(function () {
    Server::boot();

    $this->siteSearchIndex = SiteSearchIndex::factory()->create();
});

it('can crawl a site', function () {
    Server::activateRoutes('homePage');

    dispatch(new CrawlSiteJob($this->siteSearchIndex));

    waitForMeiliseach();

    /** @var \Spatie\SiteSearch\SearchResults\SearchResults $searchResults */
    $searchResults = SiteSearch::index($this->siteSearchIndex->name)->query('content');

    expect($searchResults->hits)->toHaveCount(1);

    /** @var \Spatie\SiteSearch\SearchResults\Hit $hit */
    $hit = $searchResults->hits[0];

    expect($hit)
        ->pageTitle->toEqual('My title')
        ->entry->toEqual('My content');
});

it('can crawl all pages', function () {
    Server::activateRoutes('chain');

    dispatch(new CrawlSiteJob($this->siteSearchIndex));

    waitForMeiliseach();

    $searchResults = SiteSearch::index($this->siteSearchIndex->name)->query('here');

    expect(hitUrls($searchResults))->toEqual([
        'http://localhost:8181/',
        'http://localhost:8181/2',
        'http://localhost:8181/3',
    ]);
});

it('can be configured not to crawl a specific url', function () {
    Server::activateRoutes('chain');

    $this->siteSearchIndex->update([
        'profile_class' => DoNotCrawlSecondLinkSearchProfile::class,
    ]);

    dispatch(new CrawlSiteJob($this->siteSearchIndex));

    waitForMeiliseach();

    $searchResults = SiteSearch::index($this->siteSearchIndex->name)->query('here');

    expect(hitUrls($searchResults))->toEqual([
        'http://localhost:8181/',
    ]);
});

it('can be configured not to index a specific url', function () {
    Server::activateRoutes('chain');

    $this->siteSearchIndex->update([
        'profile_class' => DoNotIndexSecondLinkSearchProfile::class,
    ]);

    dispatch(new CrawlSiteJob($this->siteSearchIndex));

    waitForMeiliseach();

    $searchResults = SiteSearch::index($this->siteSearchIndex->name)->query('here');

    expect(hitUrls($searchResults))->toEqual([
        'http://localhost:8181/',
        'http://localhost:8181/3',
    ]);
});
