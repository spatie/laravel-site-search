<?php

namespace Spatie\SiteSearch;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\SiteSearch\Commands\CrawlSitesCommand;
use Spatie\SiteSearch\Commands\CreateIndexCommand;

class SiteSearchServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-site-search')
            ->hasConfigFile()
            ->hasViews()
            ->hasCommands([
                CreateIndexCommand::class,
                CrawlSitesCommand::class,
                ]);
    }
}
