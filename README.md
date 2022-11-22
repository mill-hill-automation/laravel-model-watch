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
    'collections' => [
        'default' => \Mha\LaravelModelWatch\Collections\ExampleWatchUsers::class,
    ],
];
```

## Usage
### Watch a single model
Call `artisan model:watch` with a model class and ID to display the current values as a table in your console. Any time a field change is detected a new column will be added.

```bash
artisan model:watch App/Models/Contact 2 --fields=name --fields=email
```

### Watch dynamic or multiple models
With an extra couple of steps you can watch multiple models, or dynamically select which models to watch.

You can even query for models which do not yet exist, and they will appear on screen when they do.

To do this create a collection with extends `Mha\LaravelModelWatch\Collections\BaseWatchCollection` and implement the `getModels()` method, which returns a collection of models to watch.

```php
<?php

namespace App\Collections\ModelWatch;

use App\Models\User;
use Mha\LaravelModelWatch\Collections\BaseWatchCollection;
use Illuminate\Support\Collection;

class FirstUsersComments extends BaseWatchCollection
{
    /**
     * Return the user with an ID of 1 and any of their posts.
    **/
    public function getModels(): Collection
    {
        $models = new Collection;
        $user = User::find(1);

        $models[] = $user;
        $models->push(
            ...$user->comments()
        )
        return $models;
    }
}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Issues and Pull Requests are welcome, especially with tests :)

### Todo/Wishlist/Ideas:

 - Have a prompt on the command to enter events which are added to the output, to assist with tracing.

## Credits

- [Sami Walbury](https://github.com/patabugen)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
