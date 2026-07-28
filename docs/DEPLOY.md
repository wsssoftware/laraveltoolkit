# Deploy and maintenance mode

The deploy module provides an interactive two-stage release workflow for applications that use an Inertia maintenance
screen. Stage 1 enables maintenance mode and updates backend code. Stage 2 builds assets, updates the database and
caches, restarts long-running processes, then brings the application back online.

> [!CAUTION]
> The default workflow runs Git, Composer, npm, migrations, seeders, cache commands, Horizon, and PM2. Review and
> customize `config/laraveltoolkit.php` before running it on a server. In production, the Git action discards local
> tracked changes, and the Composer action runs `composer update --no-dev` rather than `composer install`.

## Setup

### 1. Publish and review the configuration

```bash
php artisan vendor:publish --tag=laraveltoolkit-config
```

The relevant defaults are:

```php
use Laraveltoolkit\Deploy\Commands\Actions\CacheApplication;
use Laraveltoolkit\Deploy\Commands\Actions\ComposerUpdate;
use Laraveltoolkit\Deploy\Commands\Actions\GitPull;
use Laraveltoolkit\Deploy\Commands\Actions\MigrateDatabase;
use Laraveltoolkit\Deploy\Commands\Actions\NpmUpdateAndBuild;
use Laraveltoolkit\Deploy\Commands\Actions\Pm2Restart;
use Laraveltoolkit\Deploy\Commands\Actions\SeedDatabase;
use Laraveltoolkit\Deploy\Commands\Actions\TerminateHorizon;
use Laraveltoolkit\Deploy\Intent;

'deploy' => [
    'domain' => env('APP_DOMAIN', 'localhost'),
    'path' => '/maintenance',
    'bypass_secret' => Str::password(10, true, true, false),
    'inertia_component' => 'Maintenance',
    'default_redirect' => '/',
    'step1' => [
        Intent::make(GitPull::class, ['--release' => '1.']),
        Intent::make(ComposerUpdate::class),
    ],
    'step2' => [
        Intent::make(NpmUpdateAndBuild::class),
        Intent::make(MigrateDatabase::class),
        Intent::make(SeedDatabase::class),
        Intent::make(CacheApplication::class),
        Intent::make(TerminateHorizon::class),
        Intent::make(Pm2Restart::class),
    ],
],
```

Remove actions your application does not use. For example, remove `SeedDatabase`, `TerminateHorizon`, or `Pm2Restart`
when those services are not part of the deployment. Arguments passed to `Intent::make()` are forwarded to the action's
Artisan command.

Set a stable bypass secret through configuration or ensure configuration is cached before deployment. Laravel uses
this secret to let an operator bypass maintenance mode.

### 2. Add the Inertia maintenance page

Create the component configured by `deploy.inertia_component`, which defaults to:

```text
resources/js/Pages/Maintenance.vue
```

A minimal component might be:

```vue
<template>
    <main>
        <h1>We'll be right back</h1>
        <p>The application is being updated. This page will refresh automatically.</p>
    </main>
</template>
```

The maintenance route uses `deploy.domain` and `deploy.path`. Make sure `APP_DOMAIN` contains a hostname such as
`app.example.com`, not a URL with a scheme.

### 3. Replace Laravel's maintenance middleware

The package middleware allows the configured maintenance page to remain reachable while the application is down:

```php
use Illuminate\Foundation\Configuration\Middleware;
use Laraveltoolkit\Deploy\PreventRequestsDuringMaintenance;

->withMiddleware(function (Middleware $middleware) {
    $middleware->replace(
        \Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance::class,
        PreventRequestsDuringMaintenance::class,
    );
})
```

### 4. Enable live maintenance events (optional)

When Laravel broadcasting and Echo are configured, the companion Vuetoolkit `Deploy()` helper listens on the public
`deploy` channel. It reacts to `server_down` and `server_up` broadcasts so connected users can move to or leave the
maintenance screen without waiting for their next request.

```js
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { Deploy } from 'laraveltoolkit';

window.Pusher = Pusher;
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

Deploy();
```

This step is optional; normal Laravel maintenance redirects still work without broadcasting.

## Running a deployment

Start the interactive selector:

```bash
php artisan deploy
```

Or invoke a stage directly:

```bash
php artisan deploy:1
php artisan deploy:2
```

Stage 1 performs these operations:

1. runs Laravel's `down` command with the configured bypass secret and maintenance redirect;
2. broadcasts `MaintenanceEnabledEvent`;
3. runs every `deploy.step1` intent;
4. remembers stage 2 as the default selection for five minutes.

Stage 2 runs every `deploy.step2` intent, calls Laravel's `up` command, and broadcasts
`MaintenanceDisabledEvent`.

> [!IMPORTANT]
> Run stage 2 even when a stage 1 action fails, or bring the application up manually with `php artisan up`. The
> built-in actions report command failures but do not guarantee an automatic rollback.

## Built-in actions

| Class | Internal signature | Behavior |
|---|---|---|
| `GitPull` | `git:pull` | Switches to a branch, pulls it, and optionally checks out the latest matching release tag. |
| `ComposerUpdate` | `composer:update` | Runs `composer update`; adds optimized autoloading and `--no-dev` in production. |
| `NpmUpdateAndBuild` | `npm:install` | Optionally runs `npm install` followed by `npm run build`. |
| `MigrateDatabase` | `deploy:migrate` | Runs forced migrations. |
| `SeedDatabase` | `deploy:seed` | Runs forced database seeders. |
| `CacheApplication` | `deploy:cache` | Clears caches, then caches routes, config, events, and views in production. |
| `TerminateHorizon` | `deploy:terminate_horizon` | Gracefully terminates Horizon workers. |
| `Pm2Restart` | `pm2:restart` | Restarts all PM2 processes with updated environment variables. |

Commands that execute in a working directory accept `--cwd`; pass it through the intent arguments when a frontend or
repository lives outside Laravel's base path. Action commands are resolved by the deployment stages and are not
registered as standalone Artisan commands by the package.

---

[Back to the documentation index](README.md)
