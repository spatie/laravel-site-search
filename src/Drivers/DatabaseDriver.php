<?php

namespace Spatie\SiteSearch\Drivers;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Spatie\SiteSearch\Drivers\Database\Grammar;
use Spatie\SiteSearch\Drivers\Database\MySqlGrammar;
use Spatie\SiteSearch\Drivers\Database\PostgresGrammar;
use Spatie\SiteSearch\Drivers\Database\SqliteGrammar;
use Spatie\SiteSearch\Models\SiteSearchConfig;
use Spatie\SiteSearch\SearchResults\Hit;
use Spatie\SiteSearch\SearchResults\SearchResults;

class DatabaseDriver implements Driver
{
    public function __construct(
        protected Connection $connection,
        protected Grammar $grammar,
    ) {}

    public static function make(SiteSearchConfig $config): self
    {
        $connectionName = $config->getExtraValue('database.connection');

        $connection = DB::connection($connectionName);

        $grammar = match ($connection->getDriverName()) {
            'sqlite' => new SqliteGrammar,
            'mysql', 'mariadb' => new MySqlGrammar,
            'pgsql' => new PostgresGrammar,
            default => throw new RuntimeException("Unsupported database driver: {$connection->getDriverName()}"),
        };

        return new self($connection, $grammar);
    }

    public function createIndex(string $indexName): self
    {
        $this->grammar->ensureFtsSetup($this->connection);

        return $this;
    }

    public function updateDocument(string $indexName, array $documentProperties): self
    {
        $this->upsertDocument($indexName, $documentProperties);

        return $this;
    }

    public function updateManyDocuments(string $indexName, array $documents): self
    {
        $consolidated = $this->consolidateDocuments($documents);

        $this->connection->transaction(function () use ($indexName, $consolidated) {
            foreach ($consolidated as $document) {
                $this->upsertDocument($indexName, $document);
            }
        });

        return $this;
    }

    public function deleteIndex(string $indexName): self
    {
        $this->connection->table('site_search_documents')
            ->where('index_name', $indexName)
            ->delete();

        return $this;
    }

    public function search(
        string $indexName,
        string $query,
        ?int $limit = null,
        int $offset = 0,
        array $searchParameters = [],
    ): SearchResults {
        $startTime = microtime(true);

        $effectiveLimit = $limit ?? 20;

        $this->grammar->ensureFtsSetup($this->connection);

        $results = $this->grammar->search($this->connection, $indexName, $query, $effectiveLimit, $offset);
        $totalCount = $this->grammar->getTotalCount($this->connection, $indexName, $query);

        $processingTimeMs = (int) ((microtime(true) - $startTime) * 1000);

        $hits = collect($results)
            ->map(fn (array $row) => new Hit($this->buildHitProperties($row)));

        return new SearchResults(
            $hits,
            $processingTimeMs,
            $totalCount,
            $effectiveLimit,
            $offset,
        );
    }

    public function allIndexNames(): array
    {
        return $this->connection->table('site_search_documents')
            ->distinct()
            ->pluck('index_name')
            ->all();
    }

    public function documentCount(string $indexName): int
    {
        return $this->connection->table('site_search_documents')
            ->where('index_name', $indexName)
            ->count();
    }

    public function isProcessing(string $indexName): bool
    {
        return false;
    }

    public function finalizeIndex(string $indexName): self
    {
        return $this;
    }

    protected function consolidateDocuments(array $documents): array
    {
        $grouped = [];

        foreach ($documents as $document) {
            $url = $document['url'] ?? '';

            if (! isset($grouped[$url])) {
                $grouped[$url] = $document;

                continue;
            }

            if (isset($document['entry']) && $document['entry'] !== '') {
                $grouped[$url]['entry'] = trim($grouped[$url]['entry']."\n".$document['entry']);
            }

            if (($grouped[$url]['anchor'] ?? null) === null && isset($document['anchor'])) {
                $grouped[$url]['anchor'] = $document['anchor'];
            }
        }

        return array_values($grouped);
    }

    protected function upsertDocument(string $indexName, array $documentProperties): void
    {
        $standardFields = ['id', 'url', 'anchor', 'pageTitle', 'h1', 'entry', 'description', 'date_modified_timestamp'];
        $extra = array_diff_key($documentProperties, array_flip($standardFields));

        $this->connection->table('site_search_documents')->upsert([
            'index_name' => $indexName,
            'document_id' => $documentProperties['id'] ?? uniqid(),
            'url' => $documentProperties['url'] ?? '',
            'anchor' => $documentProperties['anchor'] ?? null,
            'page_title' => $documentProperties['pageTitle'] ?? null,
            'h1' => $documentProperties['h1'] ?? null,
            'entry' => $documentProperties['entry'] ?? null,
            'description' => $documentProperties['description'] ?? null,
            'date_modified_timestamp' => $documentProperties['date_modified_timestamp'] ?? null,
            'extra' => ! empty($extra) ? json_encode($extra) : null,
        ], ['index_name', 'document_id'], ['url', 'anchor', 'page_title', 'h1', 'entry', 'description', 'date_modified_timestamp', 'extra']);
    }

    protected function buildHitProperties(array $row): array
    {
        $formatted = [];

        if (isset($row['entry_highlighted'])) {
            $formatted['entry'] = $row['entry_highlighted'];
        }

        if (isset($row['description_highlighted'])) {
            $formatted['description'] = $row['description_highlighted'];
        }

        $extra = [];

        if (! empty($row['extra'])) {
            $extra = json_decode($row['extra'], true) ?? [];
        }

        return array_merge([
            'id' => $row['document_id'] ?? $row['id'],
            'url' => $row['url'],
            'anchor' => $row['anchor'] ?? null,
            'pageTitle' => $row['page_title'],
            'h1' => $row['h1'],
            'entry' => $row['entry'],
            'description' => $row['description'],
            'date_modified_timestamp' => $row['date_modified_timestamp'],
            '_formatted' => $formatted,
        ], $extra);
    }
}
