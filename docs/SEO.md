# SEO metadata and robots

The SEO module builds one request-scoped metadata payload for Blade and Inertia. It supports titles, descriptions,
canonical URLs, robots directives, Open Graph, Twitter cards, crawler detection, friendly URLs, and dynamic
`robots.txt` output.

## Configure metadata

Set metadata before returning the response, usually in a controller:

```php
use Inertia\Inertia;
use Laraveltoolkit\Facades\SEO;
use Laraveltoolkit\SEO\Image;
use Laraveltoolkit\SEO\RobotRule;

public function __invoke()
{
    SEO::withTitle('Client orders')
        ->withDescription('Review and manage client orders.')
        ->withCanonical(route('orders.index'))
        ->withRobots(RobotRule::ALL)
        ->withOpenGraphType('website')
        ->withOpenGraphImage(new Image('public', 'seo/orders.webp', 'Orders dashboard'))
        ->withTwitterCardSite('@example')
        ->withTwitterCardImage(new Image('public', 'seo/orders.webp', 'Orders dashboard'));

    return Inertia::render('Orders/Index');
}
```

Images are resolved through the configured Laravel filesystem disk. Their URL receives the file's last-modified value
as a cache-busting query string when available.

## Metadata propagation

Propagation is enabled by default. Changing the main title, description, or canonical URL also updates matching Open
Graph and Twitter values:

```php
SEO::withTitle('Orders');
// Also sets Open Graph and Twitter titles.

SEO::withTitle('Orders', propagate: false);
// Changes only the main title.

SEO::withoutPropagation()
    ->withTitle('Orders');
```

Use the specific methods when networks need different content:

```php
SEO::withOpenGraphTitle('Orders on Example')
    ->withOpenGraphDescription('Browse the latest orders.')
    ->withOpenGraphUrl(route('orders.index'))
    ->withTwitterCardTitle('Latest orders')
    ->withTwitterCardDescription('Browse them on Example.');
```

Every `with...` method has a corresponding `without...` method for optional values, for example
`withoutTitle()`, `withoutCanonical()`, and `withoutOpenGraphImage()`.

When no canonical URL or Open Graph URL is configured, the payload uses the current request URL. Calling
`withoutCanonical()` explicitly suppresses the canonical link.

## Server-rendered tags

Add the Blade component to the document `<head>` so crawlers receive metadata in the initial HTML:

```blade
<head>
    <!-- ... -->
    <x-seo />
</head>
```

The component is registered automatically and renders the package's SEO view.

## Inertia and Vue

Share the payload from `HandleInertiaRequests`:

```php
use Illuminate\Http\Request;
use Laraveltoolkit\Facades\SEO;

public function share(Request $request): array
{
    return [
        ...parent::share($request),
        'seo' => fn () => SEO::payload(),
    ];
}
```

Then render Vuetoolkit's `Head` component once in the application layout so metadata follows client-side navigation:

```vue
<script setup lang="ts">
import { Head } from 'laraveltoolkit';
</script>

<template>
    <Head />
    <slot />
</template>
```

The Blade and Vue renderers are complementary: Blade covers the initial response and non-JavaScript crawlers, while
Vue updates metadata during Inertia visits.

## Robots meta directives

Pass enum values or directive strings:

```php
use Laraveltoolkit\SEO\RobotRule;

SEO::withRobots(
    RobotRule::NOINDEX,
    RobotRule::NOFOLLOW,
    'max-snippet:120',
);

SEO::withoutRobots();
```

Supported enum cases include `ALL`, `NOINDEX`, `NOFOLLOW`, `NONE`, `NOARCHIVE`, `NOSNIPPET`, `NOIMAGEINDEX`,
`NOTRANSLATE`, `INDEXIFEMBEDDED`, `MAX_SNIPPET`, `MAX_IMAGE_PREVIEW`, `MAX_VIDEO_PREVIEW`, and
`UNAVAILABLE_AFTER`.

## `robots.txt`

When default sitemap routes are enabled, the package also registers `/robots.txt`. Its behavior is:

1. `public/robots.txt` wins because the web server normally serves it before Laravel;
2. when `public/robots.stub` exists, Laravel returns its contents and appends the configured sitemap URL;
3. otherwise, Laravel generates the file from SEO configuration and request-time rules.

Configure generated rules in `config/laraveltoolkit.php` or at runtime:

```php
SEO::withRobotsTxtRule(
    userAgent: '*',
    allow: collect(['/']),
    disallow: collect(['/admin', '/account']),
);

SEO::withRobotsTxtSitemap(route('lt.sitemap'));

SEO::withoutRobotsTxtRule('Googlebot');
SEO::withoutRobotsTxtRule(); // Remove every user-agent rule.
SEO::withoutRobotsTxtSitemap(); // Restore the default sitemap route when available.
```

For sitemap registration and indexes, see the [Sitemap guide](SITEMAP.md).

## Utilities

```php
SEO::isCrawler();
SEO::isCrawler($request->userAgent());

SEO::friendlyUrlString('An example string!');
// an-example-string

SEO::friendlyUrlString('R&D @ Example', separator: '_', language: 'en');
```

Friendly URL behavior can be customized under `seo.friendly_url` in the package configuration.

## Defaults

Publish `laraveltoolkit-config` to define site-wide metadata, social images, robots rules, and propagation. Page-level
facade calls override those defaults for the current request.

---

[Back to the documentation index](README.md)
