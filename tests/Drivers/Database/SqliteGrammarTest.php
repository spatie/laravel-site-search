<?php

use Illuminate\Support\Facades\DB;
use Spatie\SiteSearch\Drivers\Database\SqliteGrammar;

beforeEach(function () {
    if (DB::connection()->getDriverName() !== 'sqlite') {
        $this->markTestSkipped('SQLite grammar tests require SQLite connection');
    }

    $this->connection = DB::connection();
    $this->grammar = new SqliteGrammar;
});

it('creates FTS virtual table and triggers', function () {
    $this->grammar->ensureFtsSetup($this->connection);

    $tables = $this->connection->select("SELECT name FROM sqlite_master WHERE type='table' AND name='site_search_documents_fts'");
    expect($tables)->toHaveCount(1);

    $triggers = $this->connection->select("SELECT name FROM sqlite_master WHERE type='trigger' AND name LIKE 'site_search_documents_%'");
    expect($triggers)->toHaveCount(3);
});

it('is idempotent when called multiple times', function () {
    $this->grammar->ensureFtsSetup($this->connection);
    $this->grammar->ensureFtsSetup($this->connection);

    $tables = $this->connection->select("SELECT name FROM sqlite_master WHERE type='table' AND name='site_search_documents_fts'");
    expect($tables)->toHaveCount(1);
});

it('syncs FTS index on insert', function () {
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

it('syncs FTS index on delete', function () {
    $this->grammar->ensureFtsSetup($this->connection);

    $this->connection->table('site_search_documents')->insert([
        'index_name' => 'test',
        'document_id' => 'doc1',
        'url' => 'https://example.com',
        'entry' => 'Laravel is a PHP framework',
    ]);

    $this->connection->table('site_search_documents')
        ->where('document_id', 'doc1')
        ->delete();

    $results = $this->grammar->search($this->connection, 'test', 'Laravel', 10, 0);

    expect($results)->toHaveCount(0);
});

it('syncs FTS index on update', function () {
    $this->grammar->ensureFtsSetup($this->connection);

    $this->connection->table('site_search_documents')->insert([
        'index_name' => 'test',
        'document_id' => 'doc1',
        'url' => 'https://example.com',
        'entry' => 'Laravel is a PHP framework',
    ]);

    $this->connection->table('site_search_documents')
        ->where('document_id', 'doc1')
        ->update(['entry' => 'Symfony is a PHP framework']);

    $laravelResults = $this->grammar->search($this->connection, 'test', 'Laravel', 10, 0);
    $symfonyResults = $this->grammar->search($this->connection, 'test', 'Symfony', 10, 0);

    expect($laravelResults)->toHaveCount(0);
    expect($symfonyResults)->toHaveCount(1);
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

it('supports porter stemming', function () {
    $this->grammar->ensureFtsSetup($this->connection);

    $this->connection->table('site_search_documents')->insert([
        'index_name' => 'test',
        'document_id' => 'doc1',
        'url' => 'https://example.com',
        'entry' => 'The runner was running quickly',
    ]);

    $results = $this->grammar->search($this->connection, 'test', 'run', 10, 0);

    expect($results)->toHaveCount(1);
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

it('respects limit and offset', function () {
    $this->grammar->ensureFtsSetup($this->connection);

    for ($i = 1; $i <= 5; $i++) {
        $this->connection->table('site_search_documents')->insert([
            'index_name' => 'test',
            'document_id' => "doc{$i}",
            'url' => "https://example.com/{$i}",
            'entry' => "Searchable content number {$i}",
        ]);
    }

    $results = $this->grammar->search($this->connection, 'test', 'searchable', 2, 0);
    expect($results)->toHaveCount(2);

    $results = $this->grammar->search($this->connection, 'test', 'searchable', 2, 3);
    expect($results)->toHaveCount(2);
});
