# Documentation

Laravel Toolkit is modular: install the package once, then configure only the features your application uses.

## Choose a feature

| Guide | Use it when you need |
|---|---|
| [Measurement](MEASUREMENT.md) | Typed values, unit conversion, localized formatting, arithmetic, or Eloquent measurement casts. |
| [ACL](ACL.md) | Policies, rules, roles, Laravel Gate abilities, or role middleware. |
| [Stored Assets](STORED_ASSETS.md) | Durable file storage tied to Eloquent models, including variants and cleanup. |
| [Utilities](UTILITIES.md) | Validation rules, document/phone helpers, regex utilities, enums, or Laravel macros. |
| [PrimeVue Data](PRIMEVUE_DATA.md) | Server-side PrimeVue DataTable or DataView filtering, sorting, and pagination. |
| [FilePond](FILEPOND.md) | Temporary or chunked browser uploads. |
| [Flash](FLASH.md) | Backend session messages displayed as PrimeVue toasts. |
| [SEO](SEO.md) | HTML metadata, Open Graph, Twitter cards, robots directives, or `robots.txt`. |
| [Sitemap](SITEMAP.md) | XML sitemaps, sitemap indexes, domain-specific URLs, or large query sets. |
| [Deploy](DEPLOY.md) | Two-stage deployments and an Inertia-aware maintenance screen. |
| [Multi-domain Inertia](MULTI_DOMAIN.md) | Inertia visits that can redirect between trusted application domains. |

## Common setup commands

```bash
# Package configuration
php artisan vendor:publish --tag=laraveltoolkit-config

# ACL and Stored Assets tables
php artisan vendor:publish --tag=laraveltoolkit-migrations
php artisan migrate

# Editable sitemap registration file
php artisan vendor:publish --tag=laraveltoolkit-sitemap
```

The package service provider is discovered automatically. You only need to register feature-specific application
code described in each guide.

## Frontend companion

Guides that import components from `laraveltoolkit` in Vue refer to the
[Vuetoolkit](https://github.com/wsssoftware/vuetoolkit) npm package. Laravel Toolkit 3.x is paired with Vuetoolkit 2.x.

---

[Back to the project README](../README.md)
