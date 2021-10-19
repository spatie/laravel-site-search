<?php

namespace Spatie\SiteSearch\Drivers;

use Spatie\SiteSearch\Models\SiteSearchConfig;
use Spatie\SiteSearch\SearchResults\SearchResults;

interface Driver
{
    public static function make(SiteSearchConfig $siteSearchConfig): self;

    public function createIndex(string $indexName): self;

    public function updateDocument(string $indexName, array $documentProperties): self;

    public function updateManyDocuments(string $indexName, array $documents): self;

    public function deleteIndex(string $indexName): self;

    public function search(string $indexName, string $query, ?int $limit = null, int $offset = 0): SearchResults;

    public function allIndexNames(): array;

    public function documentCount(string $indexName);

    public function isProcessing(string $indexName): bool;
}
