<?php

namespace Spatie\SiteSearch\Support;

use Spatie\SiteSearch\Drivers\Driver;
use Spatie\SiteSearch\Exceptions\SiteConfigNameDoesNotExist;
use Spatie\SiteSearch\Profiles\SearchProfile;

class SiteConfig
{
    public static function make(string $name): self
    {
        $siteConfig = config("site-search.sites.{$name}");

        if (is_null($siteConfig)) {
            throw SiteConfigNameDoesNotExist::make($name);
        }

        return new self($siteConfig);
    }

    public function __construct(protected array $siteConfig)
    {

    }

    public function url(): string
    {
        return $this->siteConfig['url'];
    }

    public function makeProfile(): SearchProfile
    {
        $profileClass = $this->siteConfig['profile'];

        return new $profileClass;
    }

    public function indexName(): string
    {
        return $this->siteConfig['indexName'];
    }

    public function makeDriver(): Driver
    {
        /** @var \Spatie\SiteSearch\Drivers\Driver $driverClass */
        $driverClass = $this->siteConfig['driver'];

        return $driverClass::make($this);
    }
}
