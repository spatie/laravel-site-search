<?php

namespace Tests\TestSupport\TestClasses\SearchProfiles;

use Spatie\Crawler\CrawlResponse;
use Spatie\SiteSearch\Indexers\Indexer;
use Spatie\SiteSearch\Profiles\DefaultSearchProfile;
use Tests\TestSupport\TestClasses\Indexers\IndexerWithModifiedUrl;

class ModifyUrlSearchProfile extends DefaultSearchProfile
{
    public function useIndexer(string $url, CrawlResponse $response): ?Indexer
    {
        return new IndexerWithModifiedUrl($url, $response);
    }
}
