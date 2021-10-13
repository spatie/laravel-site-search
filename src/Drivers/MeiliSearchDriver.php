<?php

namespace Spatie\SiteSearch\Drivers;

use Exception;
use MeiliSearch\Client;
use MeiliSearch\Client as MeiliSearchClient;
use MeiliSearch\Endpoints\Indexes;
use Spatie\SiteSearch\Models\SiteSearchIndex;
use Spatie\SiteSearch\SearchResults\Hit;
use Spatie\SiteSearch\SearchResults\SearchResults;

class MeiliSearchDriver implements Driver
{
    public static function make(): self
    {
        $client = new Client('http://127.0.0.1:7700');

        return new self($client);
    }

    public function __construct(
        protected MeiliSearchClient $meilisearch,
    ) {
    }

    public function createIndex(string $indexName): self
    {
        $this->meilisearch->createIndex($indexName);

        $this->index($indexName)->updateSettings([
            'distinctAttribute' => 'url',
        ]);

        return $this;
    }

    public function updateDocument(string $indexName, array $documentProperties): self
    {
        $this->index($indexName)->addDocuments([$documentProperties]);

        return $this;
    }

    public function updateManyDocuments(string $indexName, array $documents): self
    {
        $chunks = array_chunk($documents, 1000);

        foreach ($chunks as $documents) {
            $this->index($indexName)->addDocuments($documents);
        }

        return $this;
    }

    public function deleteIndex(string $indexName): self
    {
        try {
            $this->index($indexName)->delete();
        } catch (Exception) {
        }

        return $this;
    }

    public function search(string $indexName, string $query, ?int $limit = null, int $offset = 0): SearchResults
    {
        $searchParams = [
            'limit' => $limit,
            'offset' => $offset,
            'attributesToHighlight' => ['entry', 'description'],
        ];

        $rawResults = $this
            ->index($indexName)
            ->rawSearch($query, array_filter($searchParams));

        $hits = array_map(function (array $hitProperties) {
            return new Hit(
                $hitProperties['id'],
                $hitProperties['pageTitle'] ?? '',
                $hitProperties['h1'] ?? '',
                $hitProperties['_formatted']['h1'] ?? '',
                $hitProperties['description'] ?? '',
                $hitProperties['_formatted']['description'] ?? '',
                $hitProperties['url'],
                $hitProperties['entry'],
                $hitProperties['_formatted']['entry'] ?? '',
                $hitProperties['date_modified_timestamp'],
                $hitProperties['extra'] ?? [],
            );
        }, $rawResults['hits']);

        return new SearchResults($hits, $rawResults['processingTimeMs']);
    }

    protected function index(string $indexName): Indexes
    {
        return $this->meilisearch->index($indexName);
    }

    public function isProcessing(string $indexName): bool
    {
        $statusUpdates = $this->meilisearch
            ->getIndex($indexName)
            ->getAllUpdateStatus();

        return collect($statusUpdates)
            ->map(fn(array $updateProperties) => $updateProperties['status'])
            ->filter(fn(string $status) => $status === 'processing')
            ->isNotEmpty();
    }
}
