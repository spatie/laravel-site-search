<?php

namespace Tests\TestSupport\TestClasses\SearchProfiles;

use Spatie\Crawler\CrawlResponse;
use Spatie\SiteSearch\Indexers\Indexer;
use Spatie\SiteSearch\Profiles\DefaultSearchProfile;
use Tests\TestSupport\TestClasses\Indexers\IndexerWithExtraInfo;

class SearchProfileWithCustomIndexer extends DefaultSearchProfile
{
    public function useIndexer(string $url, CrawlResponse $response): ?Indexer
    {
        return new IndexerWithExtraInfo($url, $response);
    }
}
