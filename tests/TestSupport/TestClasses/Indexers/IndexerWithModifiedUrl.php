<?php

namespace Tests\TestSupport\TestClasses\Indexers;

use Psr\Http\Message\UriInterface;
use Spatie\SiteSearch\Indexers\DefaultIndexer;

class IndexerWithModifiedUrl extends DefaultIndexer
{
    public function url(): UriInterface
    {
        return $this->url->withQuery('');
    }
}
