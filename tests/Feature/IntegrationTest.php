<?php

use Illuminate\Pagination\Paginator;
use Spatie\SiteSearch\Jobs\CrawlSiteJob;
use Spatie\SiteSearch\Models\SiteSearchConfig;
use Spatie\SiteSearch\SearchIndexQuery;
use Tests\TestSupport\Server\Server;
use Tests\TestSupport\TestClasses\SearchProfiles\DoNotCrawlSecondLinkSearchProfile;
use Tests\TestSupport\TestClasses\SearchProfiles\DoNotIndexSecondLinkSearchProfile;
use Tests\TestSupport\TestClasses\SearchProfiles\SearchProfileWithCustomIndexer;

beforeEach(function () {
    Server::boot();

    $this->siteSearchConfig = SiteSearchConfig::factory()->create();
});

it('can crawl a site', function () {
    Server::activateRoutes('homePage');

    dispatch(new CrawlSiteJob($this->siteSearchConfig));

    waitForMeilisearch($this->siteSearchConfig);

    $searchResults = SearchIndexQuery::onIndex($this->siteSearchConfig->name)
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

    dispatch(new CrawlSiteJob($this->siteSearchConfig));

    waitForMeilisearch($this->siteSearchConfig);

    $searchResults = SearchIndexQuery::onIndex($this->siteSearchConfig->name)
        ->search('here')
        ->get();

    expect(hitUrls($searchResults))->toEqual([
        'http://localhost:8181/',
        'http://localhost:8181/2',
        'http://localhost:8181/3',
    ]);
});

it('can use a search profile to not to crawl a specific url', function () {
    Server::activateRoutes('chain');

    $this->siteSearchConfig->update([
        'profile_class' => DoNotCrawlSecondLinkSearchProfile::class,
    ]);

    dispatch(new CrawlSiteJob($this->siteSearchConfig));

    waitForMeilisearch($this->siteSearchConfig);

    $searchResults = SearchIndexQuery::onIndex($this->siteSearchConfig->name)
        ->search('here')
        ->get();

    expect(hitUrls($searchResults))->toEqual([
        'http://localhost:8181/',
    ]);
});

it('can use a search profile not to index a specific url', function () {
    Server::activateRoutes('chain');

    $this->siteSearchConfig->update([
        'profile_class' => DoNotIndexSecondLinkSearchProfile::class,
    ]);

    dispatch(new CrawlSiteJob($this->siteSearchConfig));

    waitForMeilisearch($this->siteSearchConfig);

    $searchResults = SearchIndexQuery::onIndex($this->siteSearchConfig->name)
        ->search('here')
        ->get();

    expect(hitUrls($searchResults))->toEqual([
        'http://localhost:8181/',
        'http://localhost:8181/3',
    ]);
});

it('can be configured not to crawl certain urls', function () {
    Server::activateRoutes('chain');

    config()->set('site-search.do_not_crawl_urls', [
         '/2',
    ]);

    dispatch(new CrawlSiteJob($this->siteSearchConfig));

    waitForMeilisearch($this->siteSearchConfig);

    $searchResults = SearchIndexQuery::onIndex($this->siteSearchConfig->name)
        ->search('here')
        ->get();

    expect(hitUrls($searchResults))->toEqual([
        'http://localhost:8181/',
    ]);
});

it('can be configured not to index certain urls', function () {
    Server::activateRoutes('chain');

    config()->set('site-search.ignore_content_on_urls', [
        '/2',
    ]);

    dispatch(new CrawlSiteJob($this->siteSearchConfig));

    waitForMeilisearch($this->siteSearchConfig);

    $searchResults = SearchIndexQuery::onIndex($this->siteSearchConfig->name)
        ->search('here')
        ->get();

    expect(hitUrls($searchResults))->toEqual([
        'http://localhost:8181/',
        'http://localhost:8181/3',
    ]);
});

it('will only crawl pages that start with the crawl url', function () {
    Server::activateRoutes('subPage');

    $this->siteSearchConfig->update([
        'crawl_url' => 'http://localhost:8181/docs',
    ]);

    dispatch(new CrawlSiteJob($this->siteSearchConfig));

    waitForMeilisearch($this->siteSearchConfig);

    $searchResults = SearchIndexQuery::onIndex($this->siteSearchConfig->name)
        ->search('here')
        ->get();

    expect(hitUrls($searchResults))->toEqual([
        'http://localhost:8181/docs',
        'http://localhost:8181/docs/sub-page',
    ]);
});

it('can will not index pages with a certain header', function () {
    Server::activateRoutes('doNotIndexHeader');

    dispatch(new CrawlSiteJob($this->siteSearchConfig));

    waitForMeilisearch($this->siteSearchConfig);

    $searchResults = SearchIndexQuery::onIndex($this->siteSearchConfig->name)
        ->search('here')
        ->get();

    expect(hitUrls($searchResults))->toEqual([
        'http://localhost:8181/',
    ]);
});

it('can paginate the results', function () {
    Server::activateRoutes('chain');

    dispatch(new CrawlSiteJob($this->siteSearchConfig));

    waitForMeilisearch($this->siteSearchConfig);

    $paginator = SearchIndexQuery::onIndex($this->siteSearchConfig->name)
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

    $paginator = SearchIndexQuery::onIndex($this->siteSearchConfig->name)
        ->search('here')
        ->paginate(2);

    expect(hitUrls($paginator))->toEqual([
        'http://localhost:8181/3',
    ]);
});

it('can limit results', function () {
    Server::activateRoutes('chain');

    dispatch(new CrawlSiteJob($this->siteSearchConfig));

    waitForMeilisearch($this->siteSearchConfig);

    $searchResults = SearchIndexQuery::onIndex($this->siteSearchConfig->name)
        ->search('here')
        ->limit(2)
        ->get();

    expect(hitUrls($searchResults))->toEqual([
        'http://localhost:8181/',
        'http://localhost:8181/2',
    ]);
});

it('can handle invalid html', function () {
    Server::activateRoutes('invalidHtml');

    dispatch(new CrawlSiteJob($this->siteSearchConfig));

    waitForMeilisearch($this->siteSearchConfig);

    $searchResults = SearchIndexQuery::onIndex($this->siteSearchConfig->name)
        ->search('here')
        ->get();

    expect(hitUrls($searchResults))->toEqual([
        'http://localhost:8181/',
    ]);
});

it('can add extra properties', function () {
    $this->siteSearchConfig->update(['profile_class' => SearchProfileWithCustomIndexer::class]);

    Server::activateRoutes('homePage');

    dispatch(new CrawlSiteJob($this->siteSearchConfig));

    waitForMeilisearch($this->siteSearchConfig);

    $firstHit = SearchIndexQuery::onIndex($this->siteSearchConfig->name)
        ->search('content')
        ->get()
        ->hits->first();

    expect($firstHit->extraName)->toEqual('extraValue');
});

it('synonyms can be specified by customizing the index settings', function () {
    $this->siteSearchConfig->update(['profile_class' => SearchProfileWithCustomIndexer::class]);

    $extraValue = [
        'meilisearch' => [
            'indexSettings' => [
                'synonyms' => [
                    'Macintosh' => ['computer'],
                ],
            ],
        ],
    ];

    $this->siteSearchConfig->update(['extra' => $extraValue]);

    Server::activateRoutes('synonym');

    dispatch(new CrawlSiteJob($this->siteSearchConfig));

    waitForMeilisearch($this->siteSearchConfig);

    $firstHit = SearchIndexQuery::onIndex($this->siteSearchConfig->name)
        ->search('macintosh')
        ->get()
        ->hits->first();


    expect($firstHit->entry)->toEqual('I am a computer');
});
