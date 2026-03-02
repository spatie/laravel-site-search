<?php

namespace Tests\TestSupport\TestClasses\SearchProfiles;

use Spatie\Crawler\CrawlResponse;
use Spatie\SiteSearch\Profiles\DefaultSearchProfile;

class DoNotIndexSecondLinkSearchProfile extends DefaultSearchProfile
{
    public function shouldIndex(string $url, CrawlResponse $response): bool
    {
        ray('indexing...');

        return ! str_ends_with(parse_url($url, PHP_URL_PATH), '2');
    }
}
