<?php

namespace Mha\LaravelModelWatch;

use Mha\LaravelModelWatch\Commands\LaravelModelWatchCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaravelModelWatchServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-model-watch')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel-model-watch_table')
            ->hasCommand(LaravelModelWatchCommand::class);
    }
}
