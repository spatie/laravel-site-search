<?php

namespace Tests\TestSupport;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\LaravelRay\RayServiceProvider;
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
                return '\\Tests\\TestSupport\\Factories\\'.class_basename($modelName).'Factory';
            }
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            SiteSearchServiceProvider::class,
            RayServiceProvider::class,
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
        $driver = env('DB_DRIVER', 'sqlite');

        match ($driver) {
            'mysql' => $this->setUpMysql(),
            'pgsql' => $this->setUpPostgres(),
            default => $this->setUpSqlite(),
        };

        if ($driver !== 'sqlite') {
            Schema::dropIfExists('site_search_documents');
            Schema::dropIfExists('site_search_configs');
        }

        $class = include __DIR__.'/../../database/migrations/create_site_search_configs_table.php.stub';
        $class->up();

        $class = include __DIR__.'/../../database/migrations/create_site_search_documents_table.php.stub';
        $class->up();

        return $this;
    }

    protected function setUpSqlite(): void
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUpMysql(): void
    {
        config()->set('database.default', 'mysql');
        config()->set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'site_search_test'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ]);
    }

    protected function setUpPostgres(): void
    {
        config()->set('database.default', 'pgsql');
        config()->set('database.connections.pgsql', [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'site_search_test'),
            'username' => env('DB_USERNAME', 'postgres'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
        ]);
    }

    public function setUpViews(): self
    {
        view()->addNamespace('test', __DIR__.'/resources/views');

        return $this;
    }
}
