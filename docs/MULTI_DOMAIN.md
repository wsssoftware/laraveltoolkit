## Multi Domain application

### 1. Publishing Laravel CORS config file

if you don't have the Laravel cors config, then publish it using:

```bash
php artisan config:publish cors
```

### 2. Editing the CORS config file

On config file edit the follow lines:

```php
<?php

return [
    // Replace this:
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    // By this 
    'paths' => ['*'],

    'allowed_methods' => ['*'],

    // Here allow only origins that um trust
    'allowed_origins' => [
        env('APP_URL', 'http://localhost'),
        // ...
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    // Replace this:
    'exposed_headers' => [], 
     // By this 
    'exposed_headers' => ['x-inertia', 'x-inertia-location'],

    'max_age' => 0,

    'supports_credentials' => false,

];
```

### 3. Add middleware

You will need a middleware that changes a default redirect or visit by the `Inertia::location()` when needed.

On your `bootstrap/app.php` file prepend the `Laraveltoolkit\Inertia\HandleInertiaCrossDomainVisits` middleware:

```php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(
            append: [
                HandleInertiaRequests::class,
                AddLinkHeadersForPreloadedAssets::class,
            ],
            prepend: [
                HandleInertiaCrossDomainVisits::class,
            ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

### 4. NGINX

If you are using NGINX as your server, it will manage yours `OPTIONS` request, so, they will not processed by Laravel Cors. To Avoid this add this snipped to you site configuration:

```nginx
    if ($request_method = OPTIONS) {
        rewrite ^ /index.php last;
    }
```



