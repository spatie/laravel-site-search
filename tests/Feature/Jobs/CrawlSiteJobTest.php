<?php

use Illuminate\Pagination\Paginator;
use Spatie\SiteSearch\Jobs\CrawlSiteJob;
use Spatie\SiteSearch\Models\SiteSearchIndex;
use Spatie\SiteSearch\SearchIndexQuery;
use Tests\TestSupport\Server\Server;
use Tests\TestSupport\TestClasses\SearchProfiles\DoNotCrawlSecondLinkSearchProfile;
use Tests\TestSupport\TestClasses\SearchProfiles\DoNotIndexSecondLinkSearchProfile;

beforeEach(function () {
    Server::boot();

    $this->siteSearchIndex = SiteSearchIndex::factory()->create();
});

it('can crawl a site', function () {
    Server::activateRoutes('homePage');

    dispatch(new CrawlSiteJob($this->siteSearchIndex));

    waitForMeilisearch($this->siteSearchIndex);

    $searchResults = SearchIndexQuery::onIndex($this->siteSearchIndex->name)
        ->search('content')
        ->get();

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

    waitForMeilisearch($this->siteSearchIndex);

    $searchResults = SearchIndexQuery::onIndex($this->siteSearchIndex->name)
        ->search('here')
        ->get();

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

    waitForMeilisearch($this->siteSearchIndex);

    $searchResults = SearchIndexQuery::onIndex($this->siteSearchIndex->name)
        ->search('here')
        ->get();

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

    waitForMeilisearch($this->siteSearchIndex);

    $searchResults = SearchIndexQuery::onIndex($this->siteSearchIndex->name)
        ->search('here')
        ->get();

    expect(hitUrls($searchResults))->toEqual([
        'http://localhost:8181/',
        'http://localhost:8181/3',
    ]);
});

it('will only crawl pages that start with the crawl url', function () {
    Server::activateRoutes('subPage');

    $this->siteSearchIndex->update([
        'crawl_url' => 'http://localhost:8181/docs',
    ]);

    dispatch(new CrawlSiteJob($this->siteSearchIndex));

    waitForMeilisearch($this->siteSearchIndex);

    $searchResults = SearchIndexQuery::onIndex($this->siteSearchIndex->name)
        ->search('here')
        ->get();

    expect(hitUrls($searchResults))->toEqual([
        'http://localhost:8181/docs',
        'http://localhost:8181/docs/sub-page',
    ]);
});

it('can will not index pages with a certain header', function () {
    Server::activateRoutes('doNotIndexHeader');

    dispatch(new CrawlSiteJob($this->siteSearchIndex));

    waitForMeilisearch($this->siteSearchIndex);

    $searchResults = SearchIndexQuery::onIndex($this->siteSearchIndex->name)
        ->search('here')
        ->get();

    expect(hitUrls($searchResults))->toEqual([
        'http://localhost:8181/',
    ]);
});

it('can paginate the results', function () {
    Server::activateRoutes('chain');

    dispatch(new CrawlSiteJob($this->siteSearchIndex));

    waitForMeilisearch($this->siteSearchIndex);

    $paginator = SearchIndexQuery::onIndex($this->siteSearchIndex->name)
        ->search('here')
        ->paginate(2);

    expect(hitUrls($paginator))->toEqual([
        'http://localhost:8181/',
        'http://localhost:8181/2',
    ]);

    // fake that we're on page 2
    Paginator::currentPageResolver(function () {
        return 2;
    });

    $paginator = SearchIndexQuery::onIndex($this->siteSearchIndex->name)
        ->search('here')
        ->paginate(2);

    expect(hitUrls($paginator))->toEqual([
        'http://localhost:8181/3',
    ]);
});

it('can limit results', function () {
    Server::activateRoutes('chain');

    dispatch(new CrawlSiteJob($this->siteSearchIndex));

    waitForMeilisearch($this->siteSearchIndex);

    $searchResults = SearchIndexQuery::onIndex($this->siteSearchIndex->name)
        ->search('here')
        ->limit(2)
        ->get();

    expect(hitUrls($searchResults))->toEqual([
        'http://localhost:8181/',
        'http://localhost:8181/2',
    ]);
});

it('can handle invalid html', function() {
    Server::activateRoutes('invalidHtml');

    dispatch(new CrawlSiteJob($this->siteSearchIndex));

    waitForMeilisearch($this->siteSearchIndex);

    $searchResults = SearchIndexQuery::onIndex($this->siteSearchIndex->name)
        ->search('here')
        ->get();

    expect(hitUrls($searchResults))->toEqual([
        'http://localhost:8181/',
    ]);
});
