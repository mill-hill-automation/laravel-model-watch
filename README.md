# Adds a model:watch command to poll the database for changes to Eloquent models

[![Latest Version on Packagist](https://img.shields.io/packagist/v/mill-hill-automation/laravel-model-watch.svg?style=flat-square)](https://packagist.org/packages/mill-hill-automation/laravel-model-watch)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/mill-hill-automation/laravel-model-watch/run-tests?label=tests)](https://github.com/mill-hill-automation/laravel-model-watch/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/mill-hill-automation/laravel-model-watch/Fix%20PHP%20code%20style%20issues?label=code%20style)](https://github.com/mill-hill-automation/laravel-model-watch/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/mill-hill-automation/laravel-model-watch.svg?style=flat-square)](https://packagist.org/packages/mill-hill-automation/laravel-model-watch)

Adds a `artisan model:watch` command to watch for changes to Eloquent models by polling the database. 

## Installation

You can install the package via composer:

```bash
composer require mill-hill-automation/laravel-model-watch
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

## Usage

Call `artisan model:watch` with a model class and ID to display the current values as a table in your console. Any time a field change is detected a new column will be added.  

```bash
artisan model:watch App/Models/Contact 2 --fields=name --fields=email
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Issues and Pull Requests are welcome, especially with tests :)

### Todo/Wishlist:

 - Monitor more than one model
 - Have a config, or other way to define which models to watch in PHP
 - Support composite primary keys

## Credits

- [Sami Walbury](https://github.com/patabugen)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
