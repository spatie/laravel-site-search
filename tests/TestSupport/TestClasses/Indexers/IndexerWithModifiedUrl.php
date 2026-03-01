<?php

namespace Tests\TestSupport\TestClasses\Indexers;

use Spatie\SiteSearch\Indexers\DefaultIndexer;

class IndexerWithModifiedUrl extends DefaultIndexer
{
    public function url(): string
    {
        return strtok($this->url, '?');
    }
}
