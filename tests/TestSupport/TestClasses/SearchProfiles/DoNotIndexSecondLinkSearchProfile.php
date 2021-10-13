<?php

namespace Tests\TestSupport\TestClasses\SearchProfiles;

use Psr\Http\Message\UriInterface;
use Spatie\SiteSearch\Profiles\DefaultSearchProfile;

class DoNotIndexSecondLinkSearchProfile extends DefaultSearchProfile
{
    public function shouldIndex(UriInterface $url): bool
    {
        ray('indexing...');
        return ! str_ends_with($url->getPath(), 2);
    }
}
