<?php

use Illuminate\Pagination\Paginator;
use Spatie\SiteSearch\Drivers\MeiliSearchDriver;
use Spatie\SiteSearch\Models\SiteSearchConfig;
use Spatie\SiteSearch\SearchResults\Hit;
use Spatie\SiteSearch\SearchResults\SearchResults;
use Tests\TestSupport\TestCase;

uses(TestCase::class)
    ->beforeEach(fn () => ray()->clearScreen())
    ->in(__DIR__);

function waitForMeilisearch(SiteSearchConfig $siteSearchConfig): void
{
    $indexName = $siteSearchConfig->refresh()->index_name;

    while (MeiliSearchDriver::make($siteSearchConfig)->isProcessing($indexName)) {
        sleep(1);
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
