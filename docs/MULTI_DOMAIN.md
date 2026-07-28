# Multi-domain Inertia visits

Browsers cannot complete a normal Inertia XHR visit when navigation crosses hostnames. The
`HandleInertiaCrossDomainVisits` middleware detects an Inertia `GET` request whose host differs from the previous URL
and returns `Inertia::location()`, forcing a full browser visit to the destination.

Use this guide only when one Laravel application serves multiple trusted domains or subdomains.

## 1. Publish Laravel's CORS configuration

If `config/cors.php` does not exist:

```bash
php artisan config:publish cors
```

## 2. Allow the trusted application origins

Make sure the routes involved in navigation are covered and expose Inertia's response headers:

```php
return [
    'paths' => ['*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        env('APP_URL', 'http://localhost'),
        env('ADMIN_URL', 'http://admin.localhost'),
    ],

    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],

    'exposed_headers' => [
        'x-inertia',
        'x-inertia-location',
    ],

    'max_age' => 0,
    'supports_credentials' => false,
];
```

> [!CAUTION]
> List exact trusted origins. Do not combine wildcard origins with credentialed requests. If the domains must share
> an authenticated session, configure Laravel's session cookie domain, Sanctum/stateful domains where applicable,
> frontend credentials, and `supports_credentials` as a coordinated security decision.

Narrow `paths` and `allowed_methods` when your routing structure allows it; the broad values above support navigation
across all web routes.

## 3. Prepend the middleware

In `bootstrap/app.php`, run the package middleware before the rest of the `web` stack:

```php
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Laraveltoolkit\Inertia\HandleInertiaCrossDomainVisits;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(
            prepend: [
                HandleInertiaCrossDomainVisits::class,
            ],
        );
    })
    ->create();
```

The middleware leaves non-Inertia requests, non-`GET` requests, and same-host visits unchanged.

## 4. Forward preflight requests through Nginx when needed

Some Nginx configurations answer `OPTIONS` before Laravel can add CORS headers. If that applies to your server,
forward preflight requests to the front controller:

```nginx
if ($request_method = OPTIONS) {
    rewrite ^ /index.php last;
}
```

Prefer configuring CORS in one layer only. If Nginx already returns the complete, correct CORS response, Laravel does
not need to process the preflight.

## Verify the integration

From one configured origin, trigger an Inertia link to the other host and inspect the browser network panel. The
cross-host request should return an Inertia location response, after which the browser performs a full document
navigation. Same-host links should remain normal client-side Inertia visits.

---

[Back to the documentation index](README.md)
