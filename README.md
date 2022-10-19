# Adds a model:watch command to poll the database for changes to Eloquent models

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mill-hill-automation/laravel-model-watch.svg?style=flat-square)](https://packagist.org/packages/mill-hill-automation/laravel-model-watch)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/mill-hill-automation/laravel-model-watch/run-tests?label=tests)](https://github.com/mill-hill-automation/laravel-model-watch/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/mill-hill-automation/laravel-model-watch/Fix%20PHP%20code%20style%20issues?label=code%20style)](https://github.com/mill-hill-automation/laravel-model-watch/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/mill-hill-automation/laravel-model-watch.svg?style=flat-square)](https://packagist.org/packages/mill-hill-automation/laravel-model-watch)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/laravel-model-watch.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/laravel-model-watch)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require mill-hill-automation/laravel-model-watch
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-model-watch-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-model-watch-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="laravel-model-watch-views"
```

## Usage

```php
$laravelModelWatch = new Mha\LaravelModelWatch();
echo $laravelModelWatch->echoPhrase('Hello, Mha!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Sami Walbury](https://github.com/patabugen)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
