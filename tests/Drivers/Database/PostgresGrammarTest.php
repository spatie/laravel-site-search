<?php

use Illuminate\Support\Facades\DB;
use Spatie\SiteSearch\Drivers\Database\PostgresGrammar;

beforeEach(function () {
    if (DB::connection()->getDriverName() !== 'pgsql') {
        $this->markTestSkipped('PostgreSQL grammar tests require PostgreSQL connection');
    }

    $this->connection = DB::connection();
    $this->grammar = new PostgresGrammar;
});

it('creates text search config, tsvector column and GIN index', function () {
    $this->grammar->ensureFtsSetup($this->connection);

    $config = $this->connection->select("
        SELECT 1 FROM pg_ts_config WHERE cfgname = 'site_search'
    ");

    expect($config)->toHaveCount(1);

    $columns = $this->connection->select("
        SELECT column_name FROM information_schema.columns
        WHERE table_name = 'site_search_documents' AND column_name = 'search_vector'
    ");

    expect($columns)->toHaveCount(1);
});

it('is idempotent when called multiple times', function () {
    $this->grammar->ensureFtsSetup($this->connection);
    $this->grammar->ensureFtsSetup($this->connection);

    $columns = $this->connection->select("
        SELECT column_name FROM information_schema.columns
        WHERE table_name = 'site_search_documents' AND column_name = 'search_vector'
    ");

    expect($columns)->toHaveCount(1);
});

it('can search documents', function () {
    $this->grammar->ensureFtsSetup($this->connection);

    $this->connection->table('site_search_documents')->insert([
        'index_name' => 'test',
        'document_id' => 'doc1',
        'url' => 'https://example.com',
        'entry' => 'Laravel is a PHP framework',
    ]);

    $results = $this->grammar->search($this->connection, 'test', 'Laravel', 10, 0);

    expect($results)->toHaveCount(1);
    expect($results[0]['document_id'])->toBe('doc1');
});

it('filters by index name', function () {
    $this->grammar->ensureFtsSetup($this->connection);

    $this->connection->table('site_search_documents')->insert([
        ['index_name' => 'index-a', 'document_id' => 'doc1', 'url' => 'https://a.com', 'entry' => 'Content for A'],
        ['index_name' => 'index-b', 'document_id' => 'doc2', 'url' => 'https://b.com', 'entry' => 'Content for B'],
    ]);

    $resultsA = $this->grammar->search($this->connection, 'index-a', 'Content', 10, 0);
    $resultsB = $this->grammar->search($this->connection, 'index-b', 'Content', 10, 0);

    expect($resultsA)->toHaveCount(1);
    expect($resultsA[0]['url'])->toBe('https://a.com');

    expect($resultsB)->toHaveCount(1);
    expect($resultsB[0]['url'])->toBe('https://b.com');
});

it('returns highlighted entry and description', function () {
    $this->grammar->ensureFtsSetup($this->connection);

    $this->connection->table('site_search_documents')->insert([
        'index_name' => 'test',
        'document_id' => 'doc1',
        'url' => 'https://example.com',
        'entry' => 'The Laravel framework is great for building web apps',
        'description' => 'A brief description about Laravel',
    ]);

    $results = $this->grammar->search($this->connection, 'test', 'Laravel', 10, 0);

    expect($results[0]['entry_highlighted'])->toContain('<em>Laravel</em>');
    expect($results[0]['description_highlighted'])->toContain('<em>Laravel</em>');
});

it('returns all documents for empty query', function () {
    $this->grammar->ensureFtsSetup($this->connection);

    $this->connection->table('site_search_documents')->insert([
        ['index_name' => 'test', 'document_id' => 'doc1', 'url' => 'https://example.com/1', 'entry' => 'First'],
        ['index_name' => 'test', 'document_id' => 'doc2', 'url' => 'https://example.com/2', 'entry' => 'Second'],
    ]);

    $results = $this->grammar->search($this->connection, 'test', '', 10, 0);

    expect($results)->toHaveCount(2);
});

it('supports prefix matching', function () {
    $this->grammar->ensureFtsSetup($this->connection);

    $this->connection->table('site_search_documents')->insert([
        'index_name' => 'test',
        'document_id' => 'doc1',
        'url' => 'https://example.com',
        'entry' => 'Authentication and authorization',
    ]);

    $results = $this->grammar->search($this->connection, 'test', 'auth', 10, 0);

    expect($results)->toHaveCount(1);
});

it('indexes common words without stop word filtering', function () {
    $this->grammar->ensureFtsSetup($this->connection);

    $this->connection->table('site_search_documents')->insert([
        'index_name' => 'test',
        'document_id' => 'doc1',
        'url' => 'https://example.com',
        'entry' => 'Here is the content of page one',
    ]);

    $results = $this->grammar->search($this->connection, 'test', 'here', 10, 0);

    expect($results)->toHaveCount(1);
});

it('correctly counts total results', function () {
    $this->grammar->ensureFtsSetup($this->connection);

    for ($i = 1; $i <= 5; $i++) {
        $this->connection->table('site_search_documents')->insert([
            'index_name' => 'test',
            'document_id' => "doc{$i}",
            'url' => "https://example.com/{$i}",
            'entry' => "Searchable content number {$i}",
        ]);
    }

    $totalCount = $this->grammar->getTotalCount($this->connection, 'test', 'searchable');

    expect($totalCount)->toBe(5);
});
