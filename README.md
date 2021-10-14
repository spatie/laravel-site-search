**THIS PACKAGE IS IN DEVELOPMENT, DO NOT USE IN PRODUCTION YET**

# A site search engine

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-site-search.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-site-search)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/spatie/laravel-site-search/run-tests?label=tests)](https://github.com/spatie/laravel-site-search/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/spatie/laravel-site-search/Check%20&%20fix%20styling?label=code%20style)](https://github.com/spatie/laravel-site-search/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-site-search.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-site-search)

This package can crawl your entire site and index it. 

TODO:
- make `extra` on indexer work (+ __get on search hit)
- consider renaming model
- finish docs

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/laravel-site-search.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/laravel-site-search)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require spatie/laravel-site-search
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Spatie\SiteSearch\SiteSearchServiceProvider" --tag="laravel-site-search-config"
```

This is the contents of the published config file:

```php
return [
    'sites' => [
        'default' => [
            'url' => env('APP_URL'),
            'driver' => Spatie\SiteSearch\Drivers\MeiliSearchDriver::class,
            'index_name' => 'default-site-search-index',
            'profile' => Spatie\SiteSearch\Profiles\DefaultSearchProfile::class,
        ],
    ],
];

```

## Usage

Coming soon...

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [freek](https://github.com/freekmurze)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
