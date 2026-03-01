# Upgrading

## From v2 to v3

### Requirements

- PHP 8.4 or higher (was 8.3)
- Laravel 12 or higher (Laravel 10 and 11 are no longer supported)
- spatie/crawler 8.5 or higher

### Breaking Changes

#### 1. Crawler signature changes (UriInterface to string, ResponseInterface to CrawlResponse)

**Likelihood of impact:** 🔴 **HIGH** - Affects users with custom search profiles or custom crawl observers

The underlying `spatie/crawler` package now passes plain `string` URLs instead of `UriInterface` instances, and `CrawlResponse` instead of `ResponseInterface`.

If you have a custom `SearchProfile` implementation, update all method signatures:

**Before:**
```php
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

class MySearchProfile implements SearchProfile
{
    public function shouldCrawl(UriInterface $url): bool
    {
        return ! str_contains((string) $url, '/admin');
    }

    public function shouldIndex(UriInterface $url, ResponseInterface $response): bool
    {
        return $response->getStatusCode() === 200;
    }

    public function useIndexer(UriInterface $url, ResponseInterface $response): ?Indexer
    {
        // ...
    }
}
```

**After:**
```php
use Spatie\Crawler\CrawlResponse;

class MySearchProfile implements SearchProfile
{
    public function shouldCrawl(string $url): bool
    {
        return ! str_contains($url, '/admin');
    }

    public function shouldIndex(string $url, CrawlResponse $response): bool
    {
        return $response->status() === 200;
    }

    public function useIndexer(string $url, CrawlResponse $response): ?Indexer
    {
        // ...
    }
}
```

Key changes:
- `UriInterface $url` becomes `string $url`. You no longer need to cast to string or call `->getPath()`. Use `parse_url($url, PHP_URL_PATH)` if you need the path.
- `ResponseInterface $response` becomes `CrawlResponse $response`. Use `$response->status()` instead of `$response->getStatusCode()`. If you need the underlying PSR response, call `$response->toPsrResponse()`.

#### 2. `Indexer::entries()` return type change

**Likelihood of impact:** 🔴 **HIGH** - Affects users with custom indexers

If you have a custom indexer implementing the `Indexer` interface, update the `entries()` method to return an array of arrays instead of an array of strings.

**Before:**
```php
public function entries(): array
{
    return [
        'First paragraph text...',
        'Second paragraph text...',
    ];
}
```

**After:**
```php
public function entries(): array
{
    return [
        ['text' => 'First paragraph text...', 'anchor' => null],
        ['text' => 'Second paragraph text...', 'anchor' => 'section-id'],
    ];
}
```

The `text` key is required and contains the content to index. The `anchor` key is optional and contains the heading ID for deep linking.

#### 3. Event property changes

**Likelihood of impact:** 🟡 **MEDIUM** - Affects users listening to package events

`IndexedUrlEvent` and `FailedToCrawlUrlEvent` now use `string` instead of `UriInterface` for URLs, and include a `CrawlProgress` property.

**Before:**
```php
Event::listen(function (IndexedUrlEvent $event) {
    $url = (string) $event->url;
    $status = $event->response->getStatusCode();
});
```

**After:**
```php
Event::listen(function (IndexedUrlEvent $event) {
    $url = $event->url;
    $status = $event->response->status();
    $progress = $event->progress; // new CrawlProgress property
});
```

A new `CrawlFinishedEvent` is also available, fired when crawling completes. It contains a `FinishReason` enum and `CrawlProgress`.

#### 4. `Driver` interface: new `finalizeIndex()` method

**Likelihood of impact:** 🟡 **MEDIUM** - Affects users with custom drivers

The `Driver` interface now requires a `finalizeIndex()` method and `documentCount()` must return `int`.

```php
public function finalizeIndex(string $indexName): self
{
    // For most drivers, this can be a no-op
    return $this;
}

public function documentCount(string $indexName): int
{
    // Must return int (previously had no return type)
}
```

#### 5. New database columns

**Likelihood of impact:** 🟢 **LOW** - Requires a migration

Three new columns have been added to the `site_search_configs` table. Create a migration:

```bash
php artisan make:migration add_crawl_progress_to_site_search_configs_table
```

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('site_search_configs', function (Blueprint $table) {
            $table->integer('urls_found')->default(0);
            $table->integer('urls_failed')->default(0);
            $table->string('finish_reason')->nullable();
        });
    }
};
```

These columns are automatically populated after each crawl and shown in `site-search:list`.

#### 6. `SqliteDriver` replaced by `DatabaseDriver`

**Likelihood of impact:** 🔴 **HIGH** - Affects all users using the default driver

The `SqliteDriver` has been replaced by `DatabaseDriver`, which uses your application's database for full-text search. It supports SQLite, MySQL, and PostgreSQL.

If you were using the default `SqliteDriver`, update your config:

**Before:**
```php
'default_driver' => Spatie\SiteSearch\Drivers\SqliteDriver::class,
```

**After:**
```php
'default_driver' => Spatie\SiteSearch\Drivers\DatabaseDriver::class,
```

The new driver stores documents in a `site_search_documents` table instead of separate `.sqlite` files. Publish and run the new migration:

```bash
php artisan vendor:publish --tag="site-search-migrations"
php artisan migrate
```

Then re-index your sites:

```bash
php artisan site-search:crawl
```

After re-indexing, you can safely delete the old `storage/site-search` directory.

If you were using a custom `database.connection` in the `extra` config, the key has changed from `sqlite.storage_path` to `database.connection`.

### New Features

After upgrading, you can:

1. **Use the database driver** (now the default). Supports SQLite, MySQL, and PostgreSQL with no external dependencies.
   ```php
   'default_driver' => Spatie\SiteSearch\Drivers\DatabaseDriver::class,
   ```

2. **Deep linking**. Search results now include anchor links to specific sections. Use `$hit->urlWithAnchor()` to get URLs like `https://example.com/page#section-id`. Requires re-indexing.

3. **Crawl progress tracking**. The `site-search:list` command now shows URLs found, failed, and crawl status for each index.

### Upgrade Steps

1. Update the package:
   ```bash
   composer update spatie/laravel-site-search
   ```

2. Run the database migration (see breaking change #5 above)

3. Update any custom search profiles, indexers, drivers, or event listeners per the breaking changes above

4. Re-index your sites to populate the new columns and anchor data:
   ```bash
   php artisan site-search:crawl
   ```

## From v1 to v2

The only breaking change is the upgrade from crawler v7 to v8. In v8 of crawler, the observer classes gained a `linkText` parameter. If you have extended the `SearchProfileCrawlObserver` class, you will need to add this parameter to your methods.
