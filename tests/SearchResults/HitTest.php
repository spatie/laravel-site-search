<?php

use Spatie\SiteSearch\SearchResults\Hit;

it('returns url with anchor when anchor is present', function () {
    $hit = new Hit([
        'url' => 'https://example.com/page',
        'anchor' => 'section-id',
        'pageTitle' => 'Test Page',
        'entry' => 'Test content',
    ]);

    expect($hit->urlWithAnchor())->toBe('https://example.com/page#section-id');
});

it('returns base url when anchor is null', function () {
    $hit = new Hit([
        'url' => 'https://example.com/page',
        'anchor' => null,
        'pageTitle' => 'Test Page',
        'entry' => 'Test content',
    ]);

    expect($hit->urlWithAnchor())->toBe('https://example.com/page');
});

it('returns base url when anchor is empty string', function () {
    $hit = new Hit([
        'url' => 'https://example.com/page',
        'anchor' => '',
        'pageTitle' => 'Test Page',
        'entry' => 'Test content',
    ]);

    expect($hit->urlWithAnchor())->toBe('https://example.com/page');
});

it('returns base url when anchor key is missing', function () {
    $hit = new Hit([
        'url' => 'https://example.com/page',
        'pageTitle' => 'Test Page',
        'entry' => 'Test content',
    ]);

    expect($hit->urlWithAnchor())->toBe('https://example.com/page');
});

it('correctly appends anchor with special characters', function () {
    $hit = new Hit([
        'url' => 'https://example.com/page',
        'anchor' => 'section-with-dashes',
        'pageTitle' => 'Test Page',
        'entry' => 'Test content',
    ]);

    expect($hit->urlWithAnchor())->toBe('https://example.com/page#section-with-dashes');
});

it('handles complex anchors', function () {
    $hit = new Hit([
        'url' => 'https://example.com/docs/installation',
        'anchor' => 'step-3-configure-database',
        'pageTitle' => 'Installation',
        'entry' => 'Configuration instructions',
    ]);

    expect($hit->urlWithAnchor())->toBe('https://example.com/docs/installation#step-3-configure-database');
});
