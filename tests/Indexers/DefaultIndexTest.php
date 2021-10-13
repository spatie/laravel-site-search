<?php

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Uri;
use Spatie\SiteSearch\Indexers\DefaultIndexer;

it('can index a page', function () {
    $indexer = new DefaultIndexer(
        new Uri('https://example.com'),
        new Response(body: view('test::page'))
    );

    expect($indexer)
        ->pageTitle()->toEqual('This is my page')
        ->description()->toEqual('This is the description')
        ->h1()->toEqual('This is the H1')
        ->entries()->toEqual([
            'This is the H1',
            'This is the content',
        ]);
});

it('can ignore content', function () {
    $indexer = new DefaultIndexer(
        new Uri('https://example.com'),
        new Response(body: view('test::noIndex'))
    );

    expect($indexer)
        ->entries()->toEqual([
            'This is the H1',
            'This is the content',
        ]);
});
