<?php

namespace Spatie\SiteSearch\Drivers;

use Psr\Log\LoggerInterface;
use Spatie\SiteSearch\Models\SiteSearchConfig;
use Spatie\SiteSearch\SearchResults\SearchResults;

class ArrayDriver implements Driver
{
    protected array $indexes = [];

    protected array $documents = [];

    public static function make(SiteSearchConfig $config): self
    {
        $logger = app(LoggerInterface::class);

        return new self($logger);
    }

    public function __construct(
        protected LoggerInterface $logger,
    ) {}

    public function createIndex(string $indexName): self
    {
        $this->logger->debug('[Site Search] Creating index', [
            'index_name' => $indexName,
        ]);

        $this->indexes[$indexName] = true;
        $this->documents[$indexName] = [];

        return $this;
    }

    public function updateDocument(string $indexName, array $documentProperties): self
    {
        $this->logger->debug('[Site Search] Updating document', [
            'index_name' => $indexName,
            'document' => $documentProperties,
        ]);

        if (! isset($this->documents[$indexName])) {
            $this->createIndex($indexName);
        }

        $documentId = $documentProperties['id'] ?? uniqid();
        $this->documents[$indexName][$documentId] = $documentProperties;

        return $this;
    }

    public function updateManyDocuments(string $indexName, array $documents): self
    {
        $this->logger->debug('[Site Search] Updating many documents', [
            'index_name' => $indexName,
            'document_count' => count($documents),
            'documents' => $documents,
        ]);

        if (! isset($this->documents[$indexName])) {
            $this->createIndex($indexName);
        }

        foreach ($documents as $document) {
            $documentId = $document['id'] ?? uniqid();
            $this->documents[$indexName][$documentId] = $document;
        }

        return $this;
    }

    public function deleteIndex(string $indexName): self
    {
        $this->logger->debug('[Site Search] Deleting index', [
            'index_name' => $indexName,
        ]);

        unset($this->indexes[$indexName]);
        unset($this->documents[$indexName]);

        return $this;
    }

    public function search(
        string $indexName,
        string $query,
        ?int $limit = null,
        int $offset = 0,
        array $searchParameters = [],
    ): SearchResults {
        $this->logger->debug('[Site Search] Performing search', [
            'index_name' => $indexName,
            'query' => $query,
            'limit' => $limit,
            'offset' => $offset,
            'search_parameters' => $searchParameters,
        ]);

        $documents = collect($this->documents[$indexName] ?? []);

        if (! empty($query)) {
            $documents = $documents->filter(function ($document) use ($query) {
                $searchableText = collect($document)->values()->implode(' ');

                return stripos($searchableText, $query) !== false;
            });
        }

        $documents = $documents->unique('url')->values();

        $total = $documents->count();
        $results = $documents->skip($offset)->take($limit ?? 20);

        return new SearchResults(
            $results,
            0,
            $total,
            $limit ?? 20,
            $offset,
        );
    }

    public function isProcessing(string $indexName): bool
    {
        $this->logger->debug('[Site Search] Checking if processing', [
            'index_name' => $indexName,
        ]);

        return false;
    }

    public function allIndexNames(): array
    {
        $this->logger->debug('[Site Search] Getting all index names');

        return array_keys($this->indexes);
    }

    public function documentCount(string $indexName): int
    {
        $this->logger->debug('[Site Search] Getting document count', [
            'index_name' => $indexName,
        ]);

        return count($this->documents[$indexName] ?? []);
    }

    public function finalizeIndex(string $indexName): self
    {
        // ArrayDriver doesn't use temp files, no-op here
        return $this;
    }
}
