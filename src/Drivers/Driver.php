<?php

namespace Spatie\SiteSearch\Drivers;

use Spatie\SiteSearch\SearchResults\SearchResults;

interface Driver
{
    public function update(array $documentProperties): self;

    public function updateMany(array $documents): self;

    public function delete(): self;

    public function createIndex(): self;

    public function search(string $query): SearchResults;
}
