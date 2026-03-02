<?php

use Spatie\Crawler\CrawlResponse;
use Spatie\SiteSearch\Indexers\DefaultIndexer;

it('can index a page', function () {
    $indexer = new DefaultIndexer(
        'https://example.com',
        CrawlResponse::fake(body: view('test::page'))
    );

    expect($indexer)
        ->pageTitle()->toEqual('This is my page')
        ->description()->toEqual('This is the description')
        ->h1()->toEqual('This is the H1')
        ->entries()->toEqual([
            ['text' => 'This is the H1', 'anchor' => null],
            ['text' => 'This is the content', 'anchor' => null],
        ]);
});

it('can ignore content', function () {
    $indexer = new DefaultIndexer(
        'https://example.com',
        CrawlResponse::fake(body: view('test::noIndex'))
    );

    expect($indexer)
        ->entries()->toEqual([
            ['text' => 'This is the H1', 'anchor' => null],
            ['text' => 'This is the content', 'anchor' => null],
        ]);
});

it('extracts anchors from headings', function () {
    $indexer = new DefaultIndexer(
        'https://example.com',
        CrawlResponse::fake(body: view('test::withAnchors'))
    );

    $entries = $indexer->entries();

    // Find the entries with their anchors
    $mainTitleEntry = collect($entries)->first(fn ($e) => $e['text'] === 'Main Title');
    $introEntry = collect($entries)->first(fn ($e) => $e['text'] === 'This is the introduction paragraph');
    $sectionOneEntry = collect($entries)->first(fn ($e) => $e['text'] === 'Section One');
    $sectionTwoEntry = collect($entries)->first(fn ($e) => $e['text'] === 'Section Two');
    $subsectionEntry = collect($entries)->first(fn ($e) => $e['text'] === 'Subsection');
    $subsectionContent = collect($entries)->first(fn ($e) => $e['text'] === 'This is the subsection content');

    expect($mainTitleEntry['anchor'])->toBe('main-title');
    expect($introEntry['anchor'])->toBe('main-title'); // Inherits from h1
    expect($sectionOneEntry['anchor'])->toBe('section-one');
    expect($sectionTwoEntry['anchor'])->toBe('section-two');
    expect($subsectionEntry['anchor'])->toBe('subsection');
    expect($subsectionContent['anchor'])->toBe('subsection'); // Inherits from h3
});

it('handles headings without id attributes', function () {
    $indexer = new DefaultIndexer(
        'https://example.com',
        CrawlResponse::fake(body: view('test::withAnchors'))
    );

    $entries = $indexer->entries();

    // Find content after "Heading Without ID"
    $contentAfterNoId = collect($entries)->first(fn ($e) => $e['text'] === 'Content after heading without ID');

    // Heading without ID resets the anchor
    expect($contentAfterNoId['anchor'])->toBeNull();
});
