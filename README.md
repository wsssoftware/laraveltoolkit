# A modern toolkit of components and utilities for Laravel applications, developed by WSS Software.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/wsssoftware/laraveltoolkit.svg?style=flat-square)](https://packagist.org/packages/wsssoftware/laraveltoolkit)
[![run-tests](https://github.com/wsssoftware/laraveltoolkit/actions/workflows/run-tests.yml/badge.svg?branch=2.x)](https://github.com/wsssoftware/laraveltoolkit/actions/workflows/run-tests.yml)
[![Fix PHP code style issues](https://github.com/wsssoftware/laraveltoolkit/actions/workflows/fix-php-code-style-issues.yml/badge.svg)](https://github.com/wsssoftware/laraveltoolkit/actions/workflows/fix-php-code-style-issues.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/wsssoftware/laraveltoolkit.svg?style=flat-square)](https://packagist.org/packages/wsssoftware/laraveltoolkit)
[![codecov](https://codecov.io/gh/wsssoftware/laraveltoolkit/branch/2.x/graph/badge.svg?token=nzaXcoyc3q)](https://codecov.io/gh/wsssoftware/laraveltoolkit)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

> [!IMPORTANT]
> Some features from this package work in association with the
> package [vuetoolkit](https://github.com/wsssoftware/vuetoolkit). For mor information read it's related docs.

## Installation

You can install the package via composer:

```bash
composer require wsssoftware/laraveltoolkit
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laraveltoolkit-config"
```

You can publish the sitemap config file with:

```bash
php artisan vendor:publish --tag="laraveltoolkit-sitemap"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

### [ACL](docs/ACL.md)

A minimalist implementation of an access control level

### [Colors](docs/COLORS.md)

A toolset of helpers for colors.

### [Deploy](docs/DEPLOY.md)

A simple deploy and maintenance mode.

### [Flash](docs/FLASH.md)

Simple flash messages from backend to front end.

### [FilePond](docs/FILEPOND.md)

A bridge between FilePond and Laravel

### [PrimeVue Data](docs/PRIMEVUE_DATA.md)

A minimalist implementation of DataTables and DataView on Laravel

### [SEO](docs/SEO.md)

Tools to help dev to handle with SEO features.

### [SiteMap](docs/SITEMAP.md)

A toolkit to automatically generate sitemaps for application

### [Stored Assets](docs/STORED_ASSETS.md)

Tools to help handle with storing assets.

## Multi domain application

If you are using a multi-domain application with InertiaJs, you can use [this guide](docs/MULTI_DOMAIN.md) to configure
your cors policies
and add a middleware to replace redirects for Inertia::location() when needed.

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

- [Allan Mariucci Carvalho](https://github.com/wsssoftware)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
