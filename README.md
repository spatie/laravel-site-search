<div align="left">
    <a href="https://spatie.be/open-source?utm_source=github&utm_medium=banner&utm_campaign=laravel-site-search">
      <picture>
        <source media="(prefers-color-scheme: dark)" srcset="https://spatie.be/packages/header/laravel-site-search/html/dark.webp">
        <img alt="Logo for laravel-site-search" src="https://spatie.be/packages/header/laravel-site-search/html/light.webp">
      </picture>
    </a>

<h1>Create a full-text search index by crawling your site</h1>

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-site-search.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-site-search)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-site-search.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-site-search)
    
</div>

This package can crawl and index your entire site. You can think of it as a private Google search. What gets crawled and indexed can be highly customized. By default, a local SQLite database with FTS5 is used, so no external dependencies are needed. Optionally, you can use Meilisearch for more advanced search features.

When crawling your site, multiple concurrent connections are used to speed up the crawling process.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/laravel-site-search.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/laravel-site-search)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Documentation

You will find full documentation on [the dedicated documentions site](https://spatie.be/docs/laravel-site-search).

## Testing

```bash
composer test
```

Some tests require a local Meilisearch instance running on port 7700.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
