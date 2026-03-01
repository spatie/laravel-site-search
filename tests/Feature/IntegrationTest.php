<?php

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Spatie\Crawler\CrawlProgress;
use Spatie\Crawler\Enums\FinishReason;
use Spatie\SiteSearch\Events\CrawlFinishedEvent;
use Spatie\SiteSearch\Events\IndexedUrlEvent;
use Spatie\SiteSearch\Jobs\CrawlSiteJob;
use Spatie\SiteSearch\Models\SiteSearchConfig;
use Spatie\SiteSearch\Search;
use Tests\TestSupport\Server\Server;
use Tests\TestSupport\TestClasses\SearchProfiles\DoNotCrawlSecondLinkSearchProfile;
use Tests\TestSupport\TestClasses\SearchProfiles\DoNotIndexSecondLinkSearchProfile;
use Tests\TestSupport\TestClasses\SearchProfiles\ModifyUrlSearchProfile;
use Tests\TestSupport\TestClasses\SearchProfiles\SearchProfileWithCustomIndexer;

dataset('drivers', [
    'sqlite' => [fn () => [
        'extra' => ['sqlite' => ['storage_path' => sys_get_temp_dir().'/site-search-test-'.uniqid()]],
    ]],
    'meilisearch' => [fn () => [
        'driver_class' => \Spatie\SiteSearch\Drivers\MeiliSearchDriver::class,
    ]],
]);

beforeEach(function () {
    Server::boot();

    $this->siteSearchConfig = SiteSearchConfig::factory()->create([
        'driver_class' => \Spatie\SiteSearch\Drivers\SqliteDriver::class,
    ]);
});

afterEach(function () {
    $storagePath = $this->siteSearchConfig->getExtraValue('sqlite.storage_path');

    if ($storagePath && is_dir($storagePath)) {
        array_map('unlink', glob("{$storagePath}/*"));
        rmdir($storagePath);
    }
});

it('can crawl a site', function (Closure $driverSetup) {
    $this->siteSearchConfig->update($driverSetup());

    Server::activateRoutes('homePage');

    dispatch(new CrawlSiteJob($this->siteSearchConfig));

    waitForDriver($this->siteSearchConfig);

    $searchResults = Search::onIndex($this->siteSearchConfig->name)
        ->query('content')
        ->get();

    expect($searchResults->hits)->toHaveCount(1);

    /** @var \Spatie\SiteSearch\SearchResults\Hit $hit */
    $hit = $searchResults->hits[0];

    expect($hit)
        ->pageTitle->toEqual('My title')
        ->entry->toEqual('My content');
})->with('drivers');

it('can crawl and index all pages', function (Closure $driverSetup) {
    $this->siteSearchConfig->update($driverSetup());

    Server::activateRoutes('chain');

    dispatch(new CrawlSiteJob($this->siteSearchConfig));

    waitForDriver($this->siteSearchConfig);

    $searchResults = Search::onIndex($this->siteSearchConfig->name)
        ->query('here')
        ->get();

    expect(hitUrls($searchResults))->toEqual([
        'http://localhost:8181/',
        'http://localhost:8181/2',
        'http://localhost:8181/3',
    ]);
})->with('drivers');

it('can determine the number of indexed urls', function (Closure $driverSetup) {
    $this->siteSearchConfig->update($driverSetup());

    Server::activateRoutes('chain');

    dispatch(new CrawlSiteJob($this->siteSearchConfig));

    waitForDriver($this->siteSearchConfig);

    expect($this->siteSearchConfig->refresh()->number_of_urls_indexed)->toEqual(3);
})->with('drivers');

it('can use a search profile to not to crawl a specific url', function (Closure $driverSetup) {
    $this->siteSearchConfig->update(array_merge($driverSetup(), [
        'profile_class' => DoNotCrawlSecondLinkSearchProfile::class,
    ]));

    Server::activateRoutes('chain');

    dispatch(new CrawlSiteJob($this->siteSearchConfig));

    waitForDriver($this->siteSearchConfig);

    $searchResults = Search::onIndex($this->siteSearchConfig->name)
        ->query('here')
        ->get();

    expect(hitUrls($searchResults))->toEqual([
        'http://localhost:8181/',
    ]);
})->with('drivers');

it('can use a search profile not to index a specific url', function (Closure $driverSetup) {
    $this->siteSearchConfig->update(array_merge($driverSetup(), [
        'profile_class' => DoNotIndexSecondLinkSearchProfile::class,
    ]));

    Server::activateRoutes('chain');

    dispatch(new CrawlSiteJob($this->siteSearchConfig));

    waitForDriver($this->siteSearchConfig);

    $searchResults = Search::onIndex($this->siteSearchConfig->name)
        ->query('here')
        ->get();

    expect(hitUrls($searchResults))->toEqual([
        'http://localhost:8181/',
        'http://localhost:8181/3',
    ]);
})->with('drivers');

it('can be configured not to crawl certain urls', function (Closure $driverSetup) {
    $this->siteSearchConfig->update($driverSetup());

    Server::activateRoutes('chain');

    config()->set('site-search.do_not_crawl_urls', [
        '/2',
    ]);

    dispatch(new CrawlSiteJob($this->siteSearchConfig));

    waitForDriver($this->siteSearchConfig);

    $searchResults = Search::onIndex($this->siteSearchConfig->name)
        ->query('here')
        ->get();

    expect(hitUrls($searchResults))->toEqual([
        'http://localhost:8181/',
    ]);
})->with('drivers');

it('can be configured not to index certain urls', function (Closure $driverSetup) {
    $this->siteSearchConfig->update($driverSetup());

    Server::activateRoutes('chain');

    config()->set('site-search.ignore_content_on_urls', [
        '/2',
    ]);

    dispatch(new CrawlSiteJob($this->siteSearchConfig));

    waitForDriver($this->siteSearchConfig);

    $searchResults = Search::onIndex($this->siteSearchConfig->name)
        ->query('here')
        ->get();

    expect(hitUrls($searchResults))->toEqual([
        'http://localhost:8181/',
        'http://localhost:8181/3',
    ]);
})->with('drivers');

it('will only crawl pages that start with the crawl url', function (Closure $driverSetup) {
    $this->siteSearchConfig->update(array_merge($driverSetup(), [
        'crawl_url' => 'http://localhost:8181/docs',
    ]));

    Server::activateRoutes('subPage');

    dispatch(new CrawlSiteJob($this->siteSearchConfig));

    waitForDriver($this->siteSearchConfig);

    $searchResults = Search::onIndex($this->siteSearchConfig->name)
        ->query('here')
        ->get();

    expect(hitUrls($searchResults))->toEqual([
        'http://localhost:8181/docs',
        'http://localhost:8181/docs/sub-page',
    ]);
})->with('drivers');

it('can will not index pages with a certain header', function (Closure $driverSetup) {
    $this->siteSearchConfig->update($driverSetup());

    Server::activateRoutes('doNotIndexHeader');

    dispatch(new CrawlSiteJob($this->siteSearchConfig));

    waitForDriver($this->siteSearchConfig);

    $searchResults = Search::onIndex($this->siteSearchConfig->name)
        ->query('here')
        ->get();

    expect(hitUrls($searchResults))->toEqual([
        'http://localhost:8181/',
    ]);
})->with('drivers');

it('can paginate the results', function (Closure $driverSetup) {
    $this->siteSearchConfig->update($driverSetup());

    Server::activateRoutes('chain');

    dispatch(new CrawlSiteJob($this->siteSearchConfig));

    waitForDriver($this->siteSearchConfig);

    $paginator = Search::onIndex($this->siteSearchConfig->name)
        ->query('here')
        ->paginate(2);

    expect(hitUrls($paginator))->toEqual([
        'http://localhost:8181/',
        'http://localhost:8181/2',
    ]);

    // fake that we're on page 2
    Paginator::currentPageResolver(function () {
        return 2;
    });

    $paginator = Search::onIndex($this->siteSearchConfig->name)
        ->query('here')
        ->paginate(2);

    expect(hitUrls($paginator))->toEqual([
        'http://localhost:8181/3',
    ]);
})->with('drivers');

it('can limit results', function (Closure $driverSetup) {
    $this->siteSearchConfig->update($driverSetup());

    Server::activateRoutes('chain');

    dispatch(new CrawlSiteJob($this->siteSearchConfig));

    waitForDriver($this->siteSearchConfig);

    $searchResults = Search::onIndex($this->siteSearchConfig->name)
        ->query('here')
        ->limit(2)
        ->get();

    expect(hitUrls($searchResults))->toEqual([
        'http://localhost:8181/',
        'http://localhost:8181/2',
    ]);
})->with('drivers');

it('can handle invalid html', function (Closure $driverSetup) {
    $this->siteSearchConfig->update($driverSetup());

    Server::activateRoutes('invalidHtml');

    dispatch(new CrawlSiteJob($this->siteSearchConfig));

    waitForDriver($this->siteSearchConfig);

    $searchResults = Search::onIndex($this->siteSearchConfig->name)
        ->query('here')
        ->get();

    expect(hitUrls($searchResults))->toEqual([
        'http://localhost:8181/',
    ]);
})->with('drivers');

it('can add extra properties', function (Closure $driverSetup) {
    $this->siteSearchConfig->update(array_merge($driverSetup(), [
        'profile_class' => SearchProfileWithCustomIndexer::class,
    ]));

    Server::activateRoutes('homePage');

    dispatch(new CrawlSiteJob($this->siteSearchConfig));

    waitForDriver($this->siteSearchConfig);

    $firstHit = Search::onIndex($this->siteSearchConfig->name)
        ->query('content')
        ->get()
        ->hits->first();

    expect($firstHit->extraName)->toEqual('extraValue');
})->with('drivers');

it('synonyms can be specified by customizing the index settings', function () {
    $this->siteSearchConfig->update([
        'profile_class' => SearchProfileWithCustomIndexer::class,
        'driver_class' => \Spatie\SiteSearch\Drivers\MeiliSearchDriver::class,
    ]);

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

    waitForDriver($this->siteSearchConfig);

    $firstHit = Search::onIndex($this->siteSearchConfig->name)
        ->query('macintosh')
        ->get()
        ->hits->first();

    expect($firstHit->entry)->toEqual('I am a computer');
});

it('can modify indexed url', function (Closure $driverSetup) {
    $this->siteSearchConfig->update(array_merge($driverSetup(), [
        'profile_class' => ModifyUrlSearchProfile::class,
    ]));

    Server::activateRoutes('modifyUrl');

    dispatch(new CrawlSiteJob($this->siteSearchConfig));

    waitForDriver($this->siteSearchConfig);

    $searchResults = Search::onIndex($this->siteSearchConfig->name)
        ->query('with query')
        ->get();

    expect(hitUrls($searchResults))->toEqual([
        'http://localhost:8181/page',
    ]);
})->with('drivers');

it('stores crawl progress and finish reason on the model', function (Closure $driverSetup) {
    $this->siteSearchConfig->update($driverSetup());

    Server::activateRoutes('chain');

    dispatch(new CrawlSiteJob($this->siteSearchConfig));

    waitForDriver($this->siteSearchConfig);

    $this->siteSearchConfig->refresh();

    expect($this->siteSearchConfig)
        ->urls_found->toBeGreaterThanOrEqual(3)
        ->urls_failed->toBe(0)
        ->finish_reason->toBe('completed');
})->with('drivers');

it('includes CrawlProgress on IndexedUrlEvent', function () {
    Server::activateRoutes('chain');

    $events = [];

    Event::listen(IndexedUrlEvent::class, function (IndexedUrlEvent $event) use (&$events) {
        $events[] = $event;
    });

    dispatch(new CrawlSiteJob($this->siteSearchConfig));

    expect($events)->not->toBeEmpty();

    $event = $events[0];

    expect($event->progress)
        ->toBeInstanceOf(CrawlProgress::class)
        ->urlsCrawled->toBeGreaterThanOrEqual(1)
        ->urlsFound->toBeGreaterThanOrEqual(1);
});

it('dispatches CrawlFinishedEvent with FinishReason and CrawlProgress', function () {
    Server::activateRoutes('homePage');

    $firedEvent = null;

    Event::listen(CrawlFinishedEvent::class, function (CrawlFinishedEvent $event) use (&$firedEvent) {
        $firedEvent = $event;
    });

    dispatch(new CrawlSiteJob($this->siteSearchConfig));

    expect($firedEvent)
        ->not->toBeNull()
        ->finishReason->toBe(FinishReason::Completed)
        ->progress->toBeInstanceOf(CrawlProgress::class)
        ->progress->urlsCrawled->toBeGreaterThanOrEqual(1);
});
