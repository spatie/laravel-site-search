<?php

use Spatie\SiteSearch\SearchResults\Hit;
use Spatie\SiteSearch\SearchResults\SearchResults;
use Tests\TestSupport\TestCase;

uses(TestCase::class)
    ->beforeEach(fn () => ray()->clearScreen())
    ->in(__DIR__);

function waitForMeilisearch(): void
{
    sleep(6);
}

function hitUrls(SearchResults $searchResults): array
{
    return collect($searchResults->hits)
        ->map(fn (Hit $hit) => $hit->url)
        ->toArray();
}
