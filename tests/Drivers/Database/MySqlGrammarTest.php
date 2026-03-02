<?php

use Illuminate\Support\Facades\DB;
use Spatie\SiteSearch\Drivers\Database\MySqlGrammar;

beforeEach(function () {
    if (! in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'])) {
        $this->markTestSkipped('MySQL grammar tests require MySQL/MariaDB connection');
    }

    $this->connection = DB::connection();
    $this->grammar = new MySqlGrammar;
});

it('creates FULLTEXT index', function () {
    $this->grammar->ensureFtsSetup($this->connection);

    $indexes = $this->connection->select(
        "SHOW INDEX FROM site_search_documents WHERE Key_name = 'site_search_documents_fulltext'"
    );

    expect($indexes)->not->toBeEmpty();
});

it('is idempotent when called multiple times', function () {
    $this->grammar->ensureFtsSetup($this->connection);
    $this->grammar->ensureFtsSetup($this->connection);

    $indexes = $this->connection->select(
        "SHOW INDEX FROM site_search_documents WHERE Key_name = 'site_search_documents_fulltext'"
    );

    expect($indexes)->not->toBeEmpty();
});

it('can search documents', function () {
    $this->grammar->ensureFtsSetup($this->connection);

    $this->connection->table('site_search_documents')->insert([
        'index_name' => 'test',
        'document_id' => 'doc1',
        'url' => 'https://example.com',
        'entry' => 'Laravel is a PHP framework for web development',
    ]);

    $results = $this->grammar->search($this->connection, 'test', 'Laravel', 10, 0);

    expect($results)->toHaveCount(1);
    expect($results[0]['document_id'])->toBe('doc1');
});

it('filters by index name', function () {
    $this->grammar->ensureFtsSetup($this->connection);

    $this->connection->table('site_search_documents')->insert([
        ['index_name' => 'index-a', 'document_id' => 'doc1', 'url' => 'https://a.com', 'entry' => 'Content about Laravel framework'],
        ['index_name' => 'index-b', 'document_id' => 'doc2', 'url' => 'https://b.com', 'entry' => 'Content about Laravel testing'],
    ]);

    $resultsA = $this->grammar->search($this->connection, 'index-a', 'Laravel', 10, 0);
    $resultsB = $this->grammar->search($this->connection, 'index-b', 'Laravel', 10, 0);

    expect($resultsA)->toHaveCount(1);
    expect($resultsA[0]['url'])->toBe('https://a.com');

    expect($resultsB)->toHaveCount(1);
    expect($resultsB[0]['url'])->toBe('https://b.com');
});

it('adds highlighting to results', function () {
    $this->grammar->ensureFtsSetup($this->connection);

    $this->connection->table('site_search_documents')->insert([
        'index_name' => 'test',
        'document_id' => 'doc1',
        'url' => 'https://example.com',
        'entry' => 'The Laravel framework is great for building web applications',
        'description' => 'A brief description about Laravel',
    ]);

    $results = $this->grammar->search($this->connection, 'test', 'Laravel', 10, 0);

    expect($results[0]['entry_highlighted'])->toContain('<em>');
    expect($results[0]['description_highlighted'])->toContain('<em>');
});

it('returns all documents for empty query', function () {
    $this->grammar->ensureFtsSetup($this->connection);

    $this->connection->table('site_search_documents')->insert([
        ['index_name' => 'test', 'document_id' => 'doc1', 'url' => 'https://example.com/1', 'entry' => 'First page'],
        ['index_name' => 'test', 'document_id' => 'doc2', 'url' => 'https://example.com/2', 'entry' => 'Second page'],
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
            'entry' => "Searchable content about Laravel number {$i}",
        ]);
    }

    $totalCount = $this->grammar->getTotalCount($this->connection, 'test', 'Laravel');

    expect($totalCount)->toBe(5);
});
