<?php

namespace Spatie\SiteSearch\Drivers;

use Spatie\SiteSearch\SearchResults\SearchResults;

interface Driver
{
    public static function make(): self;

    public function createIndex(string $indexName): self;

    public function updateDocument(string $indexName, array $documentProperties): self;

    public function updateManyDocuments(string $indexName, array $documents): self;

    public function deleteIndex(string $indexName): self;

    public function search(string $indexName, string $query, ?int $limit = null, int $offset = 0): SearchResults;
}
