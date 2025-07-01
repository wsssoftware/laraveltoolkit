<?php

namespace Laraveltoolkit;

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
            ->name('laraveltoolkit');
    }

    public function boot(): self
    {
        $this->setLocale();

        return parent::boot();
    }

    protected function setLocale(): void
    {
        setlocale(
            LC_ALL,
            config('app.locale').'.UTF-8',
            config('app.locale'),
            config('app.fallback_locale').'.UTF-8',
            config('app.fallback_locale'),
            'en.UTF-8',
            'en',
            'en_US.UTF-8',
            'en_US',
        );
    }
}
