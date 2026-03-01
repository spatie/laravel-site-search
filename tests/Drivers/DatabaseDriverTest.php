<?php

use Spatie\SiteSearch\Drivers\DatabaseDriver;
use Spatie\SiteSearch\Models\SiteSearchConfig;

beforeEach(function () {
    $this->config = SiteSearchConfig::factory()->create();
    $this->driver = DatabaseDriver::make($this->config);
});

it('can create an index', function () {
    $this->driver->createIndex('test-index');

    expect($this->driver->documentCount('test-index'))->toBe(0);
});

it('can index documents', function () {
    $this->driver->createIndex('test-index');

    $document = [
        'id' => 'doc1',
        'url' => 'https://example.com/test',
        'pageTitle' => 'Test Page',
        'h1' => 'Welcome',
        'entry' => 'This is searchable content',
        'description' => 'A test page',
        'date_modified_timestamp' => time(),
    ];

    $this->driver->updateDocument('test-index', $document);

    expect($this->driver->documentCount('test-index'))->toBe(1);
});

it('can index many documents', function () {
    $this->driver->createIndex('test-index');

    $documents = [
        [
            'id' => 'doc1',
            'url' => 'https://example.com/page1',
            'pageTitle' => 'Page One',
            'entry' => 'First page content',
            'date_modified_timestamp' => time(),
        ],
        [
            'id' => 'doc2',
            'url' => 'https://example.com/page2',
            'pageTitle' => 'Page Two',
            'entry' => 'Second page content',
            'date_modified_timestamp' => time(),
        ],
    ];

    $this->driver->updateManyDocuments('test-index', $documents);

    expect($this->driver->documentCount('test-index'))->toBe(2);
});

it('consolidates multiple documents for the same URL into a single row', function () {
    $this->driver->createIndex('test-index');

    $documents = [
        [
            'id' => 'doc1',
            'url' => 'https://example.com/page',
            'pageTitle' => 'My Page',
            'h1' => 'Welcome',
            'entry' => 'First paragraph',
            'description' => 'A description',
            'anchor' => null,
            'date_modified_timestamp' => time(),
        ],
        [
            'id' => 'doc2',
            'url' => 'https://example.com/page',
            'pageTitle' => 'My Page',
            'h1' => 'Welcome',
            'entry' => 'Second paragraph',
            'description' => 'A description',
            'anchor' => 'section-two',
            'date_modified_timestamp' => time(),
        ],
        [
            'id' => 'doc3',
            'url' => 'https://example.com/page',
            'pageTitle' => 'My Page',
            'h1' => 'Welcome',
            'entry' => 'Third paragraph',
            'description' => 'A description',
            'anchor' => 'section-three',
            'date_modified_timestamp' => time(),
        ],
    ];

    $this->driver->updateManyDocuments('test-index', $documents);

    expect($this->driver->documentCount('test-index'))->toBe(1);

    $results = $this->driver->search('test-index', 'paragraph');
    expect($results->hits)->toHaveCount(1);
    expect($results->hits->first()->url)->toBe('https://example.com/page');
    expect($results->hits->first()->entry)->toContain('First paragraph');
    expect($results->hits->first()->entry)->toContain('Second paragraph');
    expect($results->hits->first()->entry)->toContain('Third paragraph');
});

it('can search documents', function () {
    $this->driver->createIndex('test-index');

    $this->driver->updateManyDocuments('test-index', [
        [
            'id' => 'doc1',
            'url' => 'https://example.com/page1',
            'pageTitle' => 'Laravel Tutorial',
            'entry' => 'Learn Laravel framework basics',
            'date_modified_timestamp' => time(),
        ],
        [
            'id' => 'doc2',
            'url' => 'https://example.com/page2',
            'pageTitle' => 'PHP Guide',
            'entry' => 'PHP programming fundamentals',
            'date_modified_timestamp' => time(),
        ],
    ]);

    $results = $this->driver->search('test-index', 'Laravel');

    expect($results->hits)->toHaveCount(1);
    expect($results->hits->first()->url)->toBe('https://example.com/page1');
});

it('returns highlighted snippets', function () {
    $this->driver->createIndex('test-index');

    $this->driver->updateDocument('test-index', [
        'id' => 'doc1',
        'url' => 'https://example.com/test',
        'entry' => 'The quick brown fox jumps over the lazy dog',
        'date_modified_timestamp' => time(),
    ]);

    $results = $this->driver->search('test-index', 'quick brown');
    $hit = $results->hits->first();

    expect($hit->highlightedSnippet())->toContain('<em>quick</em>');
    expect($hit->highlightedSnippet())->toContain('<em>brown</em>');
});

it('can delete an index', function () {
    $this->driver->createIndex('test-index');

    $this->driver->updateDocument('test-index', [
        'id' => 'doc1',
        'url' => 'https://example.com',
        'entry' => 'Test content',
        'date_modified_timestamp' => time(),
    ]);

    expect($this->driver->documentCount('test-index'))->toBe(1);

    $this->driver->deleteIndex('test-index');

    expect($this->driver->documentCount('test-index'))->toBe(0);
});

it('handles extra fields', function () {
    $this->driver->createIndex('test-index');

    $this->driver->updateDocument('test-index', [
        'id' => 'doc1',
        'url' => 'https://example.com',
        'entry' => 'Test content',
        'date_modified_timestamp' => time(),
        'customField' => 'custom value',
        'anotherField' => 123,
    ]);

    $results = $this->driver->search('test-index', 'content');
    $hit = $results->hits->first();

    expect($hit->customField)->toBe('custom value');
    expect($hit->anotherField)->toBe(123);
});

it('returns processing as always false', function () {
    expect($this->driver->isProcessing('test-index'))->toBeFalse();
});

it('returns all index names', function () {
    $this->driver->createIndex('index-one');
    $this->driver->createIndex('index-two');

    $this->driver->updateDocument('index-one', [
        'id' => 'doc1',
        'url' => 'https://example.com/1',
        'entry' => 'Content one',
        'date_modified_timestamp' => time(),
    ]);

    $this->driver->updateDocument('index-two', [
        'id' => 'doc2',
        'url' => 'https://example.com/2',
        'entry' => 'Content two',
        'date_modified_timestamp' => time(),
    ]);

    $names = $this->driver->allIndexNames();

    expect($names)->toContain('index-one');
    expect($names)->toContain('index-two');
});

it('supports prefix matching for partial words', function () {
    $this->driver->createIndex('test-index');

    $this->driver->updateDocument('test-index', [
        'id' => 'doc1',
        'url' => 'https://example.com',
        'entry' => 'Authentication implementation',
        'date_modified_timestamp' => time(),
    ]);

    $results = $this->driver->search('test-index', 'auth');

    expect($results->hits)->toHaveCount(1);
});

it('returns search results with correct metadata', function () {
    $this->driver->createIndex('test-index');

    $this->driver->updateManyDocuments('test-index', [
        [
            'id' => 'doc1',
            'url' => 'https://example.com/1',
            'entry' => 'Searchable content here',
            'date_modified_timestamp' => time(),
        ],
        [
            'id' => 'doc2',
            'url' => 'https://example.com/2',
            'entry' => 'More searchable content',
            'date_modified_timestamp' => time(),
        ],
        [
            'id' => 'doc3',
            'url' => 'https://example.com/3',
            'entry' => 'Even more searchable content',
            'date_modified_timestamp' => time(),
        ],
    ]);

    $results = $this->driver->search('test-index', 'searchable', limit: 2, offset: 0);

    expect($results->totalCount)->toBe(3);
    expect($results->hits)->toHaveCount(2);
    expect($results->limit)->toBe(2);
    expect($results->offset)->toBe(0);
});

it('returns empty results for non-existent index', function () {
    $results = $this->driver->search('non-existent', 'query');

    expect($results->hits)->toHaveCount(0);
    expect($results->totalCount)->toBe(0);
});

it('can replace existing document with same id', function () {
    $this->driver->createIndex('test-index');

    $this->driver->updateDocument('test-index', [
        'id' => 'doc1',
        'url' => 'https://example.com',
        'entry' => 'Original content',
        'date_modified_timestamp' => time(),
    ]);

    $this->driver->updateDocument('test-index', [
        'id' => 'doc1',
        'url' => 'https://example.com',
        'entry' => 'Updated content',
        'date_modified_timestamp' => time(),
    ]);

    expect($this->driver->documentCount('test-index'))->toBe(1);

    $results = $this->driver->search('test-index', 'Updated');
    expect($results->hits)->toHaveCount(1);
});

it('keeps indexes isolated from each other', function () {
    $this->driver->createIndex('index-a');
    $this->driver->createIndex('index-b');

    $this->driver->updateDocument('index-a', [
        'id' => 'doc1',
        'url' => 'https://example.com/a',
        'entry' => 'Content for index A',
        'date_modified_timestamp' => time(),
    ]);

    $this->driver->updateDocument('index-b', [
        'id' => 'doc1',
        'url' => 'https://example.com/b',
        'entry' => 'Content for index B',
        'date_modified_timestamp' => time(),
    ]);

    $resultsA = $this->driver->search('index-a', 'Content');
    $resultsB = $this->driver->search('index-b', 'Content');

    expect($resultsA->hits)->toHaveCount(1);
    expect($resultsA->hits->first()->url)->toBe('https://example.com/a');

    expect($resultsB->hits)->toHaveCount(1);
    expect($resultsB->hits->first()->url)->toBe('https://example.com/b');
});

it('finalizeIndex returns self', function () {
    $result = $this->driver->finalizeIndex('test-index');

    expect($result)->toBe($this->driver);
});
