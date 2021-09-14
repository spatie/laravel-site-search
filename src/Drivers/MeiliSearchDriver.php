<?php

namespace Spatie\SiteSearch\Drivers;

use Exception;
use MeiliSearch\Client as MeiliSearchClient;
use MeiliSearch\Endpoints\Indexes;
use Spatie\SiteSearch\SearchResults\Hit;
use Spatie\SiteSearch\SearchResults\SearchResults;

class MeiliSearchDriver implements Driver
{
    public function __construct(
        protected MeiliSearchClient $meilisearch,
        protected string            $indexName
    ) {
    }

    public function update(array $properties): self
    {
        $this->index()->addDocuments([$properties]);

        return $this;
    }

    protected function index(): Indexes
    {
        return $this->meilisearch->index($this->indexName);
    }

    public function search(string $query): SearchResults
    {
        $rawResults = $this->index()->rawSearch($query);

        $hits = array_map(function (array $hitProperties) {
            return new Hit(
                $hitProperties['id'],
                $hitProperties['title'],
                $hitProperties['url'],
                $hitProperties['text'],
                $hitProperties['date_modified_timestamp'],
                $hitProperties['extra'] ?? [],
            );
        }, $rawResults['hits']);

        return new SearchResults($hits, $rawResults['processingTimeMs']);
    }

    public function create(): self
    {
        $this->meilisearch->createIndex($this->indexName);

        return $this;
    }

    public function delete(): self
    {
        try {
            $this->index()->delete();
        } catch (Exception $exception) {
        }



        return $this;
    }
}
