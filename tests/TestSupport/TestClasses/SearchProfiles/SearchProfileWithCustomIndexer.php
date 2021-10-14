<?php

namespace Tests\TestSupport\TestClasses\SearchProfiles;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Spatie\SiteSearch\Indexers\Indexer;
use Spatie\SiteSearch\Profiles\DefaultSearchProfile;
use Tests\TestSupport\TestClasses\Indexers\IndexerWithExtraInfo;

class SearchProfileWithCustomIndexer extends DefaultSearchProfile
{
    public function useIndexer(UriInterface $url, ResponseInterface $response): ?Indexer
    {
        return new IndexerWithExtraInfo($url, $response);
    }
}
