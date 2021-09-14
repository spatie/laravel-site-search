<?php

namespace Spatie\SiteSearch\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\SiteSearch\SiteSearchServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            SiteSearchServiceProvider::class,
        ];
    }
}
