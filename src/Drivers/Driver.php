<?php

namespace Spatie\SiteSearch\Drivers;

use Spatie\SiteSearch\SearchResults\SearchResults;
use Spatie\SiteSearch\Support\SiteConfig;

interface Driver
{
    public static function make(SiteConfig $siteConfig): self;

    public function update(array $documentProperties): self;

    public function updateMany(array $documents): self;

    public function delete(): self;

    public function createIndex(): self;

    public function search(string $query): SearchResults;
}
