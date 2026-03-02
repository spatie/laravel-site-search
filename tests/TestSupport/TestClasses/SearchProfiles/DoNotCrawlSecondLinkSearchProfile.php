<?php

namespace Tests\TestSupport\TestClasses\SearchProfiles;

use Spatie\SiteSearch\Profiles\DefaultSearchProfile;

class DoNotCrawlSecondLinkSearchProfile extends DefaultSearchProfile
{
    public function shouldCrawl(string $url): bool
    {
        return ! str_ends_with(parse_url($url, PHP_URL_PATH), '2');
    }
}
