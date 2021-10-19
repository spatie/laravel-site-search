<?php

namespace Spatie\SiteSearch\Drivers;

use Exception;
use MeiliSearch\Client;
use MeiliSearch\Client as MeiliSearchClient;
use MeiliSearch\Endpoints\Indexes;
use MeiliSearch\Exceptions\ApiException;
use Spatie\SiteSearch\Models\SiteSearchConfig;
use Spatie\SiteSearch\SearchResults\Hit;
use Spatie\SiteSearch\SearchResults\SearchResults;

class MeiliSearchDriver implements Driver
{
    public static function make(SiteSearchConfig $config): self
    {
        $url = $config->getExtraValue('meilisearch.url', 'http://127.0.0.1:7700');

        $apiKey = $config->getExtraValue('meilisearch.apiKey');

        $client = new Client($url, $apiKey);

        $settings = $config->getExtraValue('meilisearch.indexSettings', []);

        return new self($client, $settings);
    }

    public function __construct(
        protected MeiliSearchClient $meilisearch,
        protected $settings = [],
    ) {
    }

    public function createIndex(string $indexName): self
    {
        $this->meilisearch->createIndex($indexName);

        $settings = array_merge($this->settings, [
            'distinctAttribute' => 'url',
        ]);

        $this->index($indexName)->updateSettings($settings);

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

    public function search(
        string $indexName,
        string $query,
        ?int $limit = null,
        int $offset = 0
    ): SearchResults {
        $searchParams = [
            'limit' => $limit,
            'offset' => $offset,
            'attributesToHighlight' => ['entry', 'description'],
        ];

        $rawResults = $this
            ->index($indexName)
            ->rawSearch($query, array_filter($searchParams));

        $hits = array_map(
            fn (array $hitProperties) => new Hit($hitProperties),
            $rawResults['hits']
        );

        return new SearchResults(
            collect($hits),
            $rawResults['processingTimeMs'],
            $rawResults['nbHits'],
            $rawResults['limit'],
            $rawResults['offset'],
        );
    }

    protected function index(string $indexName): Indexes
    {
        return $this->meilisearch->index($indexName);
    }

    public function isProcessing(string $indexName): bool
    {
        return $this->meilisearch->index($indexName)->stats()['isIndexing'];
    }

    public function allIndexNames(): array
    {
        return array_map(
            fn (Indexes $index) => $index->getUid(),
            $this->meilisearch->getAllIndexes(),
        );
    }

    public function documentCount(string $indexName): int
    {
        try {
            return $this->meilisearch->index($indexName)->stats()['numberOfDocuments'];
        } catch (ApiException) {
            return 0;
        }
    }
}
