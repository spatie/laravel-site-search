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

    public function update(array $documentProperties): self
    {
        $this->index()->addDocuments([$documentProperties]);

        return $this;
    }

    public function updateMany(array $documents): self
    {
        $chunks = array_chunk($documents, 1000);

        foreach ($chunks as $documents) {
            $this->index()->addDocuments($documents);
        }

        return $this;
    }

    protected function index(): Indexes
    {
        return $this->meilisearch->index($this->indexName);
    }

    public function search(string $query): SearchResults
    {
        $rawResults = $this->index()->rawSearch($query, [
            'attributesToHighlight' => ['entry', 'description'],
        ]);

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

    public function createIndex(): self
    {
        $this->meilisearch->createIndex($this->indexName);

        $this->index()->updateSettings([
            'distinctAttribute' => 'url',
        ]);

        return $this;
    }

    public function delete(): self
    {
        try {
            $this->index()->delete();
        } catch (Exception) {
        }



        return $this;
    }
}
