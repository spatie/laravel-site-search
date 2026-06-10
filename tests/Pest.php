<?php

use Illuminate\Pagination\Paginator;
use MeiliSearch\Client;
use MeiliSearch\Contracts\TasksQuery;
use Spatie\SiteSearch\Drivers\MeiliSearchDriver;
use Spatie\SiteSearch\Models\SiteSearchConfig;
use Spatie\SiteSearch\SearchResults\Hit;
use Spatie\SiteSearch\SearchResults\SearchResults;
use Tests\TestSupport\TestCase;

uses(TestCase::class)
    ->beforeEach(fn () => ray()->clearScreen())
    ->in(__DIR__);

function waitForDriver(SiteSearchConfig $siteSearchConfig): void
{
    $driverClass = $siteSearchConfig->driver_class ?? config('site-search.default_driver');

    if ($driverClass !== MeiliSearchDriver::class) {
        return;
    }

    $indexName = $siteSearchConfig->refresh()->index_name;

    $client = new Client('http://127.0.0.1:7700');

    $tasks = $client->getTasks(new TasksQuery([
        'indexUids' => [$indexName],
        'statuses' => ['enqueued', 'processing'],
    ]));

    foreach ($tasks->getResults() as $task) {
        $client->waitForTask($task['uid'], 10000);
    }
}

function hitUrls(SearchResults|Paginator $searchResults): array
{
    $items = [];

    if ($searchResults instanceof SearchResults) {
        $items = $searchResults->hits;
    }

    if ($searchResults instanceof Paginator) {
        $items = $searchResults->items();
    }

    return collect($items)
        ->map(fn (Hit $hit) => $hit->url)
        ->toArray();
}
