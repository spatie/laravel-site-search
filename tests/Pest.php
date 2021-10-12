<?php

use Spatie\SiteSearch\SearchResults\Hit;
use Spatie\SiteSearch\SearchResults\SearchResults;
use Tests\TestCase;

uses(TestCase::class)
    ->beforeEach(fn () => ray()->clearScreen())
    ->in(__DIR__);

function waitForMeiliseach(): void
{
    sleep(6);
}

function hitUrls(SearchResults $searchResults): array
{
    return collect($searchResults->hits)
        ->map(fn (Hit $hit) => $hit->url)
        ->toArray();
}
