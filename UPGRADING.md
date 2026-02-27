# Upgrading

## From v2 to v3

Version 3.0.0 introduces significant new features including the SQLite driver and deep linking support. There are some breaking changes to be aware of:

### Breaking Changes

#### 1. `Indexer::entries()` return type change

**Likelihood of impact:** 🔴 **HIGH** - Only affects users with custom indexers

If you have created a custom indexer class implementing the `Indexer` interface, you must update the `entries()` method to return an array of arrays instead of an array of strings.

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

**Action required:**
- Update your custom indexer classes to return the new format
- The `text` key is required and contains the content to index
- The `anchor` key is optional and contains the heading ID for deep linking

#### 2. `Driver` interface new required method

**Likelihood of impact:** 🟡 **MEDIUM** - Only affects users with custom drivers

The `Driver` interface now requires a `finalizeIndex()` method. If you have created a custom driver, you must implement this method.

**Example implementation:**
```php
public function finalizeIndex(string $indexName): self
{
    // Perform driver-specific finalization
    // For file-based drivers: swap temp files
    // For API-based drivers: no-op
    
    return $this;
}
```

**Action required:**
- Add the `finalizeIndex()` method to your custom driver classes
- Return `$this` to maintain fluent interface
- For most drivers, this can be a no-op (just return `$this`)

#### 3. SQLite database schema change

**Likelihood of impact:** 🟢 **LOW** - Automatically handled on re-index

The SQLite driver now includes an `anchor` column in the documents table. Existing SQLite databases will continue to work but won't have anchor data until re-indexed.

**Action required:**
- No immediate action needed - existing indexes continue to work
- Run `php artisan site-search:crawl` to re-index and populate anchor data
- If you need anchor functionality immediately, re-index your sites

### New Features Available After Upgrade

After upgrading, you can:

1. **Use the SQLite driver** - No external dependencies required
   ```php
   'default_driver' => Spatie\SiteSearch\Drivers\SqliteDriver::class,
   ```

2. **Enable deep linking** - Search results now include anchor links to specific sections (requires re-indexing existing content)

### Upgrade Steps

1. Update the package:
   ```bash
   composer update spatie/laravel-site-search
   ```

2. If you have custom indexers or drivers, update them per the breaking changes above

3. If using the SQLite driver and you want anchor support:
   ```bash
   php artisan site-search:crawl
   ```

4. Update your search results UI to use `urlWithAnchor()`:
   ```php
   $url = $hit->urlWithAnchor(); // Returns: https://example.com/page#section-id
   ```

## From v1 to v2

The only breaking changes is the upgrade from crawler v7 and v8. In v8 of crawler, the observer classes gained a `linkText` parameter. If you have extended the `SearchProfileCrawlObserver` class, you will need to add this parameter to your methods.
