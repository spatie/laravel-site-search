<?php

namespace Spatie\SiteSearch\Drivers;

use Illuminate\Database\Connection;
use Spatie\SiteSearch\Drivers\Sqlite\DatabaseManager;
use Spatie\SiteSearch\Drivers\Sqlite\QueryBuilder;
use Spatie\SiteSearch\Drivers\Sqlite\SchemaManager;
use Spatie\SiteSearch\Models\SiteSearchConfig;
use Spatie\SiteSearch\SearchResults\Hit;
use Spatie\SiteSearch\SearchResults\SearchResults;

class SqliteDriver implements Driver
{
    public function __construct(
        protected DatabaseManager $databaseManager,
        protected SchemaManager $schemaManager,
        protected QueryBuilder $queryBuilder
    ) {}

    public static function make(SiteSearchConfig $config): self
    {
        $storagePath = $config->getExtraValue(
            'sqlite.storage_path',
            storage_path('site-search')
        );

        $databaseManager = new DatabaseManager($storagePath);
        $schemaManager = new SchemaManager;
        $queryBuilder = new QueryBuilder;

        return new self($databaseManager, $schemaManager, $queryBuilder);
    }

    public function createIndex(string $indexName): self
    {
        $isTemp = $this->isPendingIndex($indexName);

        $connection = $this->databaseManager->connect($indexName, $isTemp);
        $this->schemaManager->createSchema($connection);

        return $this;
    }

    public function updateDocument(string $indexName, array $documentProperties): self
    {
        $isTemp = $this->isPendingIndex($indexName);
        $connection = $this->databaseManager->connect($indexName, $isTemp);

        $this->insertDocument($connection, $documentProperties);

        return $this;
    }

    public function updateManyDocuments(string $indexName, array $documents): self
    {
        $isTemp = $this->isPendingIndex($indexName);
        $connection = $this->databaseManager->connect($indexName, $isTemp);

        $connection->transaction(function () use ($connection, $documents) {
            foreach ($documents as $document) {
                $this->insertDocument($connection, $document);
            }
        });

        return $this;
    }

    public function deleteIndex(string $indexName): self
    {
        if ($this->databaseManager->exists($indexName)) {
            $this->databaseManager->delete($indexName);
        }

        $this->databaseManager->deleteTempIfExists($indexName);

        return $this;
    }

    public function search(
        string $indexName,
        string $query,
        ?int $limit = null,
        int $offset = 0,
        array $searchParameters = []
    ): SearchResults {
        $startTime = microtime(true);

        if (! $this->databaseManager->exists($indexName) && ! $this->databaseManager->tempExists($indexName)) {
            return new SearchResults(
                collect([]),
                0,
                0,
                $limit ?? 20,
                $offset
            );
        }

        $connection = $this->databaseManager->connect($indexName);

        $results = $this->queryBuilder->search($connection, $query, null, 0, $searchParameters);
        $totalCount = $this->queryBuilder->getTotalCount($connection, $query);

        $processingTimeMs = (int) ((microtime(true) - $startTime) * 1000);

        $effectiveLimit = $limit ?? 20;

        $hits = collect($results)
            ->unique('url')
            ->values()
            ->map(function (array $row) {
                return new Hit($this->buildHitProperties($row));
            });

        $totalCount = $hits->count();

        return new SearchResults(
            $hits->slice($offset, $effectiveLimit)->values(),
            $processingTimeMs,
            $totalCount,
            $effectiveLimit,
            $offset
        );
    }

    public function isProcessing(string $indexName): bool
    {
        return false;
    }

    public function allIndexNames(): array
    {
        return $this->databaseManager->allIndexNames();
    }

    public function documentCount(string $indexName): int
    {
        if (! $this->databaseManager->exists($indexName)) {
            return 0;
        }

        $connection = $this->databaseManager->connect($indexName);

        return $this->schemaManager->documentCount($connection);
    }

    protected function insertDocument(Connection $connection, array $documentProperties): void
    {
        $standardFields = ['id', 'url', 'anchor', 'pageTitle', 'h1', 'entry', 'description', 'date_modified_timestamp'];
        $extra = array_diff_key($documentProperties, array_flip($standardFields));

        $updateColumns = ['url', 'anchor', 'page_title', 'h1', 'entry', 'description', 'date_modified_timestamp', 'extra'];

        $connection->table('documents')->upsert([
            'id' => $documentProperties['id'] ?? uniqid(),
            'url' => $documentProperties['url'] ?? '',
            'anchor' => $documentProperties['anchor'] ?? null,
            'page_title' => $documentProperties['pageTitle'] ?? null,
            'h1' => $documentProperties['h1'] ?? null,
            'entry' => $documentProperties['entry'] ?? null,
            'description' => $documentProperties['description'] ?? null,
            'date_modified_timestamp' => $documentProperties['date_modified_timestamp'] ?? null,
            'extra' => ! empty($extra) ? json_encode($extra) : null,
        ], ['id'], $updateColumns);
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
            'id' => $row['id'],
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

    protected function isPendingIndex(string $indexName): bool
    {
        return (bool) preg_match('/^.+-[a-zA-Z0-9]{10,}$/', $indexName);
    }

    /**
     * Finalize a pending index by atomically swapping the temp file to the final location.
     * This should be called after crawling is complete to make the index searchable.
     */
    public function finalizeIndex(string $indexName): self
    {
        if ($this->isPendingIndex($indexName) && $this->databaseManager->tempExists($indexName)) {
            $this->databaseManager->atomicSwap($indexName);
        }

        return $this;
    }
}
