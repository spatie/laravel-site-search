<?php

namespace Spatie\SiteSearch;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\SiteSearch\Commands\CrawlCommand;
use Spatie\SiteSearch\Commands\CreateSearchConfigCommand;
use Spatie\SiteSearch\Commands\ListCommand;

class SiteSearchServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-site-search')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_site_search_configs_table')
            ->hasCommands([
                CreateSearchConfigCommand::class,
                CrawlCommand::class,
                ListCommand::class,
            ]);
    }
}
