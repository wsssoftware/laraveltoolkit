# Access control (ACL)

The ACL module stores application-specific policies and optional roles in a one-to-one record for each user. It
registers every rule and role as a normal Laravel Gate ability, so controllers, routes, Blade, and policies can use
Laravel's standard authorization API.

Abilities follow these names:

- policy rules: `{policy-column}::{rule-key}`, for example `users::update`;
- roles: `roles::{enum-value}`, for example `roles::admin`.

## Setup

### 1. Publish and edit the migration

```bash
php artisan vendor:publish --tag=laraveltoolkit-migrations
```

Before migrating, add one nullable JSON column for every policy. Keep `id`, `roles`, and `updated_at` as generated:

```php
Schema::create('user_permissions', function (Blueprint $table) {
    $table->foreignId('id')
        ->primary()
        ->constrained('users')
        ->cascadeOnUpdate()
        ->cascadeOnDelete();

    $table->json('roles');
    $table->json('users')->nullable();
    $table->json('products')->nullable();
    $table->timestamp('updated_at')->nullable();
});
```

The permission record intentionally shares its primary key with the user. Add new nullable JSON columns in a later
migration whenever you introduce more policies.

```bash
php artisan migrate
```

### 2. Generate the permission model

```bash
php artisan make:acl-model
```

Declare policies inside the generated model:

```php
use Laraveltoolkit\ACL\UserPermission as BaseUserPermission;

final class UserPermission extends BaseUserPermission
{
    protected function declarePoliciesAndRoles(): void
    {
        self::registryPolicy('users', 'Users', 'Manage application users')
            ->crud()
            ->export();

        self::registryPolicy('products', 'Products', 'Manage the product catalog')
            ->read()
            ->create()
            ->update()
            ->rule('publish', 'Publish', 'Publish products');
    }
}
```

`crud()` adds `create`, `read`, `update`, and `delete`. Other shortcuts include `cancel`, `download`, `execute`,
`export`, `import`, `print`, `share`, and `upload`. Use `rule()` for application-specific abilities. Each shortcut and
`rule()` accepts an optional HTTP denial status.

> [!NOTE]
> The policy column declared in PHP must also exist as a nullable JSON column on `user_permissions`.

### 3. Register the model

Register the model before the application finishes booting, usually in `AppServiceProvider`:

```php
use App\Models\UserPermission;
use Laraveltoolkit\Facades\ACL;

public function boot(): void
{
    ACL::withModel(UserPermission::class);
}
```

Add `HasUserPermission` to the authenticatable user model:

```php
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laraveltoolkit\ACL\HasUserPermission;

final class User extends Authenticatable
{
    use HasUserPermission;
}
```

The relation creates the user's empty permission record on first access.

## Authorizing policy rules

Use the generated abilities through Laravel Gate or the `can` middleware:

```php
use Illuminate\Support\Facades\Gate;

Gate::allows('users::read');
Gate::authorize('products::publish');

Route::post('/products', StoreProductController::class)
    ->middleware('can:products::create');
```

## Editing permissions

Permissions may be changed individually, in bulk, or by policy:

```php
$permissions = $user->userPermission;

$permissions->grant('users::read')
    ->grant('products::publish')
    ->deny('users::delete')
    ->save();

$permissions->grantAll('products')->save();
$permissions->denyAll('users')->save();

$permissions->fillPolicies([
    'users::create' => true,
    'users::read' => true,
    'users::update' => false,
    'users::delete' => false,
]);
$permissions->save();
```

Calling `grantAll()` or `denyAll()` without a policy applies the change to every declared policy.

## Roles

Roles are optional and use a string-backed enum implementing `HasDenyResponse`:

```php
use Illuminate\Auth\Access\Response;
use Laraveltoolkit\ACL\HasDenyResponse;

enum UserRole: string implements HasDenyResponse
{
    case ADMIN = 'admin';
    case SUPPORT = 'support';

    public function denyResponse(): Response
    {
        return match ($this) {
            self::ADMIN => Response::denyAsNotFound(),
            self::SUPPORT => Response::denyWithStatus(403),
        };
    }
}
```

Register it together with the permission model:

```php
ACL::withModel(UserPermission::class)
    ->withRolesEnum(UserRole::class);
```

Then grant, revoke, and authorize roles:

```php
$permissions->grantRole(UserRole::ADMIN)->save();
$permissions->denyRole(UserRole::SUPPORT)->save();
$permissions->grantAllRoles()->save();
$permissions->denyAllRoles()->save();

Gate::authorize('roles::admin');
```

### Role middleware

After registering a roles enum, the package exposes the `user_roles` middleware alias:

```php
// Require both roles.
Route::middleware('user_roles:admin,support')->group(function () {
    // ...
});

// Require support and reject users who also have admin.
Route::middleware('user_roles:support,!admin')->group(function () {
    // ...
});
```

Every unprefixed role is required. Prefixing a role with `!` explicitly rejects users who have it.

## Sharing permissions with Inertia

Expose only Gate-ready boolean values from `HandleInertiaRequests`:

```php
use Illuminate\Http\Request;
use Laraveltoolkit\Facades\ACL;

public function share(Request $request): array
{
    return [
        ...parent::share($request),
        'auth' => [
            'user' => $request->user(),
            'acl' => fn () => ACL::gatePermissions(),
        ],
    ];
}
```

To build a permission editor, return the complete policy metadata from a controller:

```php
use Laraveltoolkit\ACL\Policy;
use Laraveltoolkit\Facades\ACL;

return Inertia::render('Users/Permissions', [
    'permissions' => ACL::permissions(
        filter: fn (Policy $policy) => ! str_starts_with($policy->column, 'internal_'),
    ),
]);
```

The companion Vuetoolkit package provides `UserPermissionsEditor`, the `$gate` helper, `Gate` component, and
`v-gate` directive:

```vue
<template>
    <Gate rule="allows" abilities="users::read">
        <UsersTable />
        <template #fallback>Access denied.</template>
    </Gate>

    <button v-gate:allows="'products::publish'">Publish</button>
    <button v-if="$gate.allows('roles::admin')">Admin</button>
</template>
```

Treat frontend checks as presentation only; always authorize the corresponding action on the server.

## Testing

Laravel's normal authorization assertions and Gate calls work with ACL abilities. When testing permission changes,
save the permission model before authorizing because Gate resolves the persisted user relation.

---

[Back to the documentation index](README.md)
