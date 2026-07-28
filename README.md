# Laravel Toolkit

[![Latest Version on Packagist](https://img.shields.io/packagist/v/wsssoftware/laraveltoolkit.svg?style=flat-square)](https://packagist.org/packages/wsssoftware/laraveltoolkit)
[![Tests](https://github.com/wsssoftware/laraveltoolkit/actions/workflows/run-tests.yml/badge.svg?branch=3.x)](https://github.com/wsssoftware/laraveltoolkit/actions/workflows/run-tests.yml)
[![Code Style](https://github.com/wsssoftware/laraveltoolkit/actions/workflows/fix-php-code-style-issues.yml/badge.svg)](https://github.com/wsssoftware/laraveltoolkit/actions/workflows/fix-php-code-style-issues.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/wsssoftware/laraveltoolkit.svg?style=flat-square)](https://packagist.org/packages/wsssoftware/laraveltoolkit)
[![Code Coverage](https://codecov.io/gh/wsssoftware/laraveltoolkit/branch/3.x/graph/badge.svg?token=nzaXcoyc3q)](https://codecov.io/gh/wsssoftware/laraveltoolkit)

A practical collection of production-ready building blocks for Laravel applications. Laravel Toolkit combines typed
measurements, ACL, stored assets, SEO and sitemap generation, FilePond uploads, deployment helpers, validation rules,
and small framework extensions behind one package.

Use only the features your application needs. The service provider is discovered automatically and registers the
package configuration, routes, migrations, commands, views, translations, and macros.

> [!IMPORTANT]
> The Vue, Inertia, and PrimeVue examples use the companion
> [Vuetoolkit](https://github.com/wsssoftware/vuetoolkit) package. Backend-only features do not require it.

## Requirements

- PHP 8.4 or newer
- Laravel 12 or 13
- Inertia Laravel 3
- PHP extensions: BCMath, DOM, and Intl
- Vuetoolkit 2.x only when using the companion frontend components

| Laravel Toolkit | Laravel | Inertia Laravel | Vuetoolkit |
|:---------------:|:-------:|:---------------:|:----------:|
| 3.x | 12.x, 13.x | 3.x | 2.x |
| 2.x | 11.x, 12.x | 2.x | 1.x |
| 1.x | 11.x, 12.x | 1.x, 2.x | — |

## Installation

Install the package with Composer:

```bash
composer require wsssoftware/laraveltoolkit
```

Publish the configuration when you need to change defaults:

```bash
php artisan vendor:publish --tag=laraveltoolkit-config
```

Features backed by database tables, such as ACL and Stored Assets, also need the package migrations:

```bash
php artisan vendor:publish --tag=laraveltoolkit-migrations
php artisan migrate
```

## Quick start

The package is a toolkit rather than a single workflow. These examples show a few features that work immediately
after installation:

```php
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Laraveltoolkit\Measurement\Enums\LengthUnit;
use Laraveltoolkit\Rules\DocumentRule;

length(2.54, 'cm')->to(LengthUnit::MILLIMETER)->value(); // 25.4

Str::applyMask('12345678901', '000.000.000-00'); // 123.456.789-01

Number::spellCurrency(42.50, in: 'BRL', locale: 'pt_BR');

$request->validate([
    'document' => ['required', DocumentRule::both()],
]);
```

For an application feature, choose a guide below and follow its setup section.

## Features

### Application building blocks

| Feature | What it provides |
|---|---|
| [Measurements](docs/MEASUREMENT.md) | Precise value objects, conversion, formatting, arithmetic, and Eloquent casts for ten measurement dimensions. |
| [ACL](docs/ACL.md) | Database-backed policies and roles integrated with Laravel Gate and optional Vue components. |
| [Stored Assets](docs/STORED_ASSETS.md) | Recipe-driven file storage, Eloquent casts, multiple asset variants, and garbage collection. |
| [Utilities](docs/UTILITIES.md) | CPF/CNPJ and phone validation, enum helpers, regex utilities, framework macros, and extended fluent objects. |

### Inertia and frontend integrations

| Feature | What it provides |
|---|---|
| [PrimeVue Data](docs/PRIMEVUE_DATA.md) | Server-side filtering, sorting, and pagination for PrimeVue DataTable and DataView. |
| [FilePond](docs/FILEPOND.md) | Temporary, chunked uploads with request conversion to Laravel uploaded files. |
| [Flash](docs/FLASH.md) | Chainable session messages consumed by the Vuetoolkit PrimeVue toast receiver. |
| [Multi-domain Inertia](docs/MULTI_DOMAIN.md) | Cross-domain Inertia redirect handling and the required CORS setup. |

### SEO and operations

| Feature | What it provides |
|---|---|
| [SEO](docs/SEO.md) | Server- and client-rendered metadata, Open Graph, Twitter cards, robots directives, and `robots.txt`. |
| [Sitemap](docs/SITEMAP.md) | Cached XML sitemaps, domain-specific entries, query chunking, and sitemap indexes. |
| [Deploy](docs/DEPLOY.md) | Two-stage deployment commands and a broadcast-aware Inertia maintenance page. |

See the [documentation index](docs/README.md) for the complete guide map and common setup commands.

## Configuration

All package settings live in `config/laraveltoolkit.php` after publishing. They are grouped by feature:

- `deploy`: maintenance route and deployment actions
- `flash`: default lifetime, closability, and toast group
- `filepond`: temporary disk, path, and garbage collection
- `seo`: metadata defaults and propagation
- `sitemap`: routes, caching, timeouts, and XML limits
- `stored_assets`: disk, model, paths, naming, and trash retention

Only publish the configuration when you need to override these defaults.

## Testing

```bash
composer test
```

Code style can be checked and fixed with:

```bash
composer format
```

## Contributing

Contributions are welcome. Read the [contribution guide](CONTRIBUTING.md) before opening a pull request.

## Changelog

See the [changelog](CHANGELOG.md) for release history and upgrade notes.

## Security

Please report security vulnerabilities through the repository's
[private security advisory form](https://github.com/wsssoftware/laraveltoolkit/security/advisories/new), not a public issue.

## Credits

- [Allan Mariucci Carvalho](https://github.com/wsssoftware)
- [All contributors](https://github.com/wsssoftware/laraveltoolkit/graphs/contributors)

## License

Laravel Toolkit is open-source software licensed under the [MIT license](LICENSE.md).
