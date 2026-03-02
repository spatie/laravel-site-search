---
title: Troubleshooting
weight: 8
---

## Using ArrayDriver for Debugging

The `ArrayDriver` is a useful debugging tool that uses an in memory array for storing indexed files, while providing comprehensive logging of all  operations. This makes it perfect for troubleshooting crawling or indexing issues and understanding what content is being processed.

### Setting up ArrayDriver

To use the ArrayDriver for debugging, update your `config/site-search.php` configuration file:

```php
'default_driver' => Spatie\SiteSearch\Drivers\ArrayDriver::class,
```

Or update the `driver_class` property on your `SiteSearchConfig` model.

The ArrayDriver will log all operations to the Laravel log file, allowing you to see exactly what is happening during the crawl and index process.

> [!WARNING]  
> The ArrayDriver is not suitable for production use as it does not persist data and is intended solely for debugging purposes.

### Running a Synchronous Crawl

To immediately see the indexing process without queuing, use the `--sync` flag. This might be useful to debug efficiently:

```bash
php artisan site-search:crawl --sync
```

No output will be shown in the console, but you can check the Laravel log file for detailed information about the crawling and indexing process when using the aforementioned `ArrayDriver`.
