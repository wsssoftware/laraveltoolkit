## ACL

A minimalist implementation of an access control level

Before you need create vendor migrations running:

```bash
php artisan vendor:publish --tag=laraveltoolkit-migrations
```

after you must create table columns for each policy that you want.

```php
/// on database/migrations/2024_10_22_104112_create_user_permissions_table
 Schema::create('user_permissions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')
        ->unique()
        ->constrained('users')
        ->cascadeOnUpdate()
        ->cascadeOnDelete();
    // User roles is an internal feature, don't use as a policy
    $table->json('roles')->default('[]');
    $table->json('users')->nullable(); // <-- HERE
    $table->json('products')->nullable(); // <-- HERE
    $table->json('categories')->nullable(); // <-- HERE
    $table->timestamp('updated_at')->nullable();
}
//...
```

> You can create alter table after as you need to create more policy columns.

after make UserPermission model running command:

```bash
php artisan make:acl-model
```

Register it on you `AppServiceProvider`

```php
use App\Models\UserPermission;
use Laraveltoolkit\Facades\ACL;

//...
public function boot(): void
{
    ACL::withModel(UserPermission::class);
    // if you want to use role system, create a string enum and declare its FQN here
     ACL::withModel(UserPermission::class)
            ->withRolesEnum(UserRole::class);
}
```

> Role Enum is a more generic way to allow or deny user to access some part of your application.
> It must be a string Enum and implement `Laraveltoolkit\ACL\HasDenyResponse` interface to be used.

On your `UserPermission` created model declare your firsts policies and rules:

```php
protected static function declarePoliciesAndRoles(): void
{
  self::registryPolicy('users', 'Users', 'Manage system users')
      ->crud()
      ->rule('export', 'Export', 'Export users')
  // OR its equivalent
  self::registryPolicy('users', 'Users', 'Manage system users')
      ->rule('create', 'Create', 'Create users')
      ->rule('read', 'Read', 'Read users')
      ->rule('update', 'Update', 'Update users')
      ->rule('delete', 'Delete', 'Delete users')
      ->rule('export', 'Export', 'Export users');
}
```

On your `User` Model add `HasUserPermission` trait to configure relations and other things:

```php

use Laraveltoolkit\ACL\HasUserPermission;

class User extends Authenticatable
{
    use HasUserPermission;
```

To use gate on frontend registry it on `HandleInertiaRequests`

```php

use Laraveltoolkit\Facades\ACL;

public function share(Request $request): array
{
    return [
        ...parent::share($request),
        'auth' [
            //...
            'acl' => fn() => ACL::gatePermissions(),
        ],
    ];
}
```

### Editing an policy

You can edit using:

```php
$user = \Illuminate\Support\Facades\Auth::user();

$userPermission = $user->userPermission

$users = $userPermission->users->create->value = true;
// OR
$userPermission->users = [
    'create' => true,
    'read' => true,
    'update' => true,
    'delete' => true,
];
// OR
$userPermission->fillPolicies([
    'users::create' => true,
    'users::read' => true,
    'users::update' => true,
    'users::delete' => true,
]);
// OR
$userPermission->grantAll();
// OR
$userPermission->grantAll('users');
// OR
$userPermission->grant('users::create');
// OR
$userPermission->grantAllRoles();
// OR
$userPermission->grantRole(UserRole::ADMIN);
// OR
$userPermission->denyAll();
// OR
$userPermission->denyAll('users');
// OR
$userPermission->deny('users::create');
// OR
$userPermission->denyAllRoles();
// OR
$userPermission->denyRole(UserRole::ADMIN);
//then
$userPermission->save();
```

If you want edit on frontend:

```php

use Laraveltoolkit\Facades\ACL;
use Laraveltoolkit\ACL\Policy;
// on controller or equivalent
public function create(=): Response
{
    return Inertia::render('Tests/Laraveltoolkit/ACL', [
        'permissions' => ACL::permissions(),
        // if you want to filter edit permissions available por users
        'permissions' => ACL::permissions(filter: function (Policy $policy) {
            return !str_starts_with($policy->column, 'admin_');
        }),
    ]);
}

public function store(Request $request): RedirectResponse
{
    $up = \Illuminate\Support\Facades\Auth::user()->userPermission;
    $up->fillPolicies($request->permissions);
    $up->save();
    return retirect()->route('index');
}
```

```vue
<template>
    <form @submit.prevent="submit">
        <UserPermissionsEditor v-model="form.permissions" :permissions="permissions"/>
        <button type="submit">Enviar</button>
    </form>
</template>

<script lang="ts">
import {defineComponent, PropType} from "vue";
import {UserPermissions, UserPermissionsEditor} from "laraveltoolkit";
import {useForm} from "@inertiajs/vue3";

export default defineComponent({
    name: "ACL" ,
    components: {UserPermissionsEditor},
    props: {
        permissions: {
            type: Array as PropType<UserPermissions>,
            required: true,
        }
    },
    data() {
        return {
            form: useForm({
                permissions: {}
            }),
        };
    },
    methods: {
        submit(): void {
            this.form.post(route('send'))
        }
    },
});
</script>
```

### Usage

On backend, you will use like a normal gate, but pay attention on ability name:

```php
// Policy rule: policyColumn + :: + ruleName
\Illuminate\Support\Facades\Gate::allows('users::create')
// Role: roles :: + roleName
\Illuminate\Support\Facades\Gate::allows('roles::admin')
```

On frontend, when you have been installed the VueJS plugin, you can use `$gate class`, `Gate component` or
`Gate directive`.

```vue
<template>
    <Gate rule="allows" abilities="users::read">
        <h1>Show this on has</h1>
        <template #fallback>
            <h1>Or show this on hasn't</h1>
        </template>
    </Gate>
    <button v-gate:allows="'users::read'">You see if you has</button>
    <button v-gate:allows="'roles::admin'">You see if you has</button>
    <button v-if="has">You see if you has</button>
</template>

<script lang="ts">
import {defineComponent} from "vue";
import {GateDirective} from "laraveltoolkit";
import {Gate} from "Laraveltoolkit";

export default defineComponent({
    name: "Example",
    components: {Gate},
    directives: {
        gate: GateDirective,
    },
    computed: {
        has(): boolean {
            return this.$gate.allows('users.read')
        }
    },
});
</script>
```

You can also use the `Middleware` to use a role to some route or route group:

```php
Route::prefix('admin-l1')->group(function() {
// Your routes here
})->middleware('user_roles:admin,admin_lvl2')

Route::prefix('admin-l2')->group(function() {
// Your routes here
})->middleware('user_roles:admin_lvl2,!admin')
```


