<?php

use Illuminate\Support\Str;
use Spatie\SiteSearch\Drivers\SqliteDriver;
use Spatie\SiteSearch\Models\SiteSearchConfig;

beforeEach(function () {
    $this->tempDir = sys_get_temp_dir().'/site-search-test-'.uniqid();
    mkdir($this->tempDir, 0755, true);

    $this->config = SiteSearchConfig::factory()->create([
        'extra' => ['sqlite' => ['storage_path' => $this->tempDir]],
    ]);

    $this->driver = SqliteDriver::make($this->config);
});

afterEach(function () {
    $files = glob("{$this->tempDir}/*");
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    if (is_dir($this->tempDir)) {
        rmdir($this->tempDir);
    }
});

it('can create an index', function () {
    $this->driver->createIndex('test-index');

    expect(file_exists("{$this->tempDir}/test-index.sqlite"))->toBeTrue();
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

    expect(file_exists("{$this->tempDir}/test-index.sqlite"))->toBeTrue();

    $this->driver->deleteIndex('test-index');

    expect(file_exists("{$this->tempDir}/test-index.sqlite"))->toBeFalse();
});

it('handles pending index pattern for atomic swap', function () {
    $pendingName = 'my-site-'.Str::random(16);

    $this->driver->createIndex($pendingName);
    $this->driver->updateDocument($pendingName, [
        'id' => 'doc1',
        'url' => 'https://example.com',
        'entry' => 'Test content',
        'date_modified_timestamp' => time(),
    ]);

    expect(file_exists("{$this->tempDir}/{$pendingName}.sqlite.tmp"))->toBeTrue();
    expect(file_exists("{$this->tempDir}/{$pendingName}.sqlite"))->toBeFalse();

    $this->driver->finalizeIndex($pendingName);

    expect(file_exists("{$this->tempDir}/{$pendingName}.sqlite"))->toBeTrue();
    expect(file_exists("{$this->tempDir}/{$pendingName}.sqlite.tmp"))->toBeFalse();

    $results = $this->driver->search($pendingName, 'content');

    expect($results->hits)->toHaveCount(1);
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
    $this->driver->createIndex('test-index');

    expect($this->driver->isProcessing('test-index'))->toBeFalse();
});

it('returns all index names', function () {
    $this->driver->createIndex('index-one');
    $this->driver->createIndex('index-two');

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

it('swaps temp to final after crawling workflow', function () {
    // Simulate the CrawlSiteJob workflow
    $baseName = 'my-site';
    $pendingName = $baseName.'-'.Str::random(16);

    // Step 1: Create the pending index (like createNewIndex in CrawlSiteJob)
    $this->driver->createIndex($pendingName);

    // Step 2: Index documents (like startCrawler in CrawlSiteJob)
    $this->driver->updateManyDocuments($pendingName, [
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
    ]);

    // At this point, only the temp file should exist
    expect(file_exists("{$this->tempDir}/{$pendingName}.sqlite.tmp"))->toBeTrue('Temp file should exist after indexing');
    expect(file_exists("{$this->tempDir}/{$pendingName}.sqlite"))->toBeFalse('Final file should not exist yet');

    // Step 3: Finalize the index (like blessNewIndex in CrawlSiteJob)
    $this->driver->finalizeIndex($pendingName);

    // The swap should have happened
    expect(file_exists("{$this->tempDir}/{$pendingName}.sqlite"))->toBeTrue('Final file should exist after finalizeIndex');
    expect(file_exists("{$this->tempDir}/{$pendingName}.sqlite.tmp"))->toBeFalse('Temp file should be gone after swap');

    // Step 4: Verify we can get document count
    $count = $this->driver->documentCount($pendingName);
    expect($count)->toBe(2);

    // Verify we can still search
    $results = $this->driver->search($pendingName, 'First');
    expect($results->hits)->toHaveCount(1);
});
