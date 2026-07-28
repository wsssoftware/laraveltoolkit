# Sitemap

The Sitemap module renders cached XML sitemaps from URLs, collections, and chunked Eloquent queries. It also supports
domain-specific definitions and sitemap indexes for large sites.

## Setup

Publish the editable sitemap registration file:

```bash
php artisan vendor:publish --tag=laraveltoolkit-sitemap
```

When `sitemap.default_routes` is enabled, the package serves:

- `/sitemap.xml` as `lt.sitemap`;
- `/sitemap-{index}.xml` as `lt.sitemap_group`;
- `/robots.txt` using the [SEO module](SEO.md).

The sitemap endpoints return `404` until `routes/sitemap.php` exists.

## Add URLs

Register entries in `routes/sitemap.php`:

```php
use Laraveltoolkit\Facades\Sitemap;
use Laraveltoolkit\Sitemap\ChangeFrequency;

Sitemap::addUrl(route('home'));

Sitemap::addUrl(
    url: route('products.index'),
    lastModified: today()->subDay(),
    changeFrequency: ChangeFrequency::DAILY,
    priority: 0.8,
);
```

`lastModified`, `changeFrequency`, and `priority` are optional. Duplicate locations are included only once.

## Collections and queries

```php
use App\Models\Product;

Sitemap::fromCollection(['about', 'contact'], function (string $page) {
    Sitemap::addUrl(route("pages.$page"));
});

Sitemap::fromQuery(
    Product::query()->where('published', true),
    function (Product $product) {
        Sitemap::addUrl(
            route('products.show', $product),
            $product->updated_at,
            ChangeFrequency::WEEKLY,
        );
    },
    count: 500,
);
```

Queries are processed with Eloquent's chunked `each()` operation unless the query already has a limit. The `count`
argument controls chunk size and defaults to 1,000.

## Multiple domains

Wrap entries that should replace the default sitemap for one request hostname:

```php
Sitemap::domain('store.example.com', function () {
    Sitemap::addUrl('https://store.example.com');
    Sitemap::addUrl('https://store.example.com/products');
});
```

When a matching domain is registered, only entries inside its callback are rendered for that hostname. Domain names
must exactly match Laravel's `$request->getHost()` value, without scheme or path. Domains cannot be nested.

## Sitemap indexes

A sitemap cannot mix URL entries with index entries. For sites approaching search-engine limits, declare URL groups
at the root of `routes/sitemap.php`, then make the main sitemap an index:

```php
Sitemap::index('products', function () {
    Sitemap::fromQuery(Product::query(), function (Product $product) {
        Sitemap::addUrl(route('products.show', $product), $product->updated_at);
    });
});

Sitemap::index('pages', function () {
    Sitemap::addUrl(route('home'));
    Sitemap::addUrl(route('about'));
});

Sitemap::addIndex('products');
Sitemap::addIndex('pages');
```

The resulting locations are:

```text
/sitemap.xml
/sitemap-products.xml
/sitemap-pages.xml
```

Do not add normal URLs outside the index groups when the main sitemap contains `addIndex()` entries.

## Configuration and caching

Publish the package configuration to change sitemap behavior:

```php
'sitemap' => [
    'cache' => 21_600,
    'default_routes' => true,
    'timeout' => null,
    'max_file_items' => 50_000,
    'max_file_size' => 50 * 1024 * 1024,
],
```

- `cache` is the XML cache lifetime in seconds; set it to `false` to disable caching.
- `timeout` optionally changes PHP's execution time while rendering.
- `max_file_items` and `max_file_size` emit warnings when exceeded; they do not split the sitemap automatically.
- `default_routes` disables all package sitemap and robots routes when `false`.

The cache key includes the hostname, index name, and modification time of `routes/sitemap.php`. Database changes do
not automatically invalidate cached XML, so clear the application cache or choose an appropriate TTL when sitemap
content changes frequently.

Every sitemap response dispatches `Laraveltoolkit\Sitemap\SitemapRequestedEvent` after rendering.

---

[Back to the documentation index](README.md)
