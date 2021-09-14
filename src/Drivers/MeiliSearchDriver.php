<?php

namespace Spatie\SiteSearch\Drivers;

use Exception;
use MeiliSearch\Client as MeiliSearchClient;
use MeiliSearch\Endpoints\Indexes;

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

    public function search(string $query): mixed
    {
        return $this->index()->rawSearch($query);
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
       } catch (Exception $exception)
       {}



        return $this;
    }
}
