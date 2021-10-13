<?php

namespace Tests\TestSupport;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Blade;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\SiteSearch\SiteSearchServiceProvider;
use function class_basename;
use function config;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            function (string $modelName) {
                return '\\Tests\\TestSupport\\Factories\\' . class_basename($modelName) . 'Factory';
            }
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            SiteSearchServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $this
            ->setUpDatabase()
            ->setUpViews();
    }

    protected function setUpDatabase(): self
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $class = include __DIR__ . '/../../database/migrations/create_site_search_indexes_table.php.stub';
        $class->up();

        return $this;
    }

    public function setUpViews(): self
    {
        view()->addNamespace('test', __DIR__ . '/resources/views');

        return $this;
    }
}
