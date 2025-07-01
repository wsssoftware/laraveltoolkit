<?php

namespace Laraveltoolkit;

use Laraveltoolkit\Commands\LaraveltoolkitCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LaraveltoolkitServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laraveltoolkit')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laraveltoolkit_table')
            ->hasCommand(LaraveltoolkitCommand::class);
    }
}
