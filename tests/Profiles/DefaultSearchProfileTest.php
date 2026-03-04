<?php

use Spatie\Crawler\CrawlResponse;
use Spatie\SiteSearch\Profiles\DefaultSearchProfile;

it('normalizes urls with query strings in shouldIndex', function () {
    $profile = new DefaultSearchProfile;

    $response = CrawlResponse::fake(status: 200, body: '<html><body>test</body></html>');

    expect($profile->shouldIndex('https://example.com/post?utm_source=newsletter', $response))->toBeTrue();
    expect($profile->shouldIndex('https://example.com/post', $response))->toBeTrue();
});

it('passes the normalized url to the indexer', function () {
    $profile = new DefaultSearchProfile;

    $response = CrawlResponse::fake(status: 200, body: '<html><body>test</body></html>');

    $indexer = $profile->useIndexer('https://example.com/post?utm_source=newsletter&utm_medium=email', $response);

    expect($indexer->url())->toEqual('https://example.com/post');
});

it('preserves fragments when normalizing urls', function () {
    $profile = new DefaultSearchProfile;

    $response = CrawlResponse::fake(status: 200, body: '<html><body>test</body></html>');

    $indexer = $profile->useIndexer('https://example.com/post?utm_source=newsletter#section', $response);

    expect($indexer->url())->toEqual('https://example.com/post#section');
});

it('leaves urls without query strings unchanged', function () {
    $profile = new DefaultSearchProfile;

    $response = CrawlResponse::fake(status: 200, body: '<html><body>test</body></html>');

    $indexer = $profile->useIndexer('https://example.com/post', $response);

    expect($indexer->url())->toEqual('https://example.com/post');
});
