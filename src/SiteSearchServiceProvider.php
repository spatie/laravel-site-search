<?php

namespace Spatie\SiteSearch;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\SiteSearch\Commands\CreateIndexCommand;
use Spatie\SiteSearch\Commands\SiteSearchCommand;

class SiteSearchServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-site-search')
            ->hasConfigFile()
            ->hasViews()
            ->hasCommand(CreateIndexCommand::class);
    }
}
