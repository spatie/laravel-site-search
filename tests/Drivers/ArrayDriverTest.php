<?php

use Spatie\SiteSearch\Drivers\ArrayDriver;
use Spatie\SiteSearch\Models\SiteSearchConfig;

it('can index documents to the array driver', function () {
    $config = SiteSearchConfig::factory()->create();
    $driver = ArrayDriver::make($config);

    $indexName = 'test_index';
    $driver->createIndex($indexName);

    $document = [
        'id' => 'doc1',
        'title' => 'Test Document',
        'content' => 'This is test content for searching',
        'url' => 'https://example.com/test',
    ];

    $driver->updateDocument($indexName, $document);

    expect($driver->documentCount($indexName))->toBe(1);

    $searchResults = $driver->search($indexName, 'test');
    expect($searchResults->totalCount)->toBe(1);
    expect($searchResults->hits->first()['title'])->toBe('Test Document');
});
