# PrimeVue Data

PrimeVue Data connects Eloquent pagination to the companion Vuetoolkit `DataTableAdapter` and `DataViewAdapter`.
Frontend pagination, sorting, and filter state are posted through Inertia; the backend `primevueData()` macro applies
them to the query and returns a standard Laravel paginator with its page name included.

## Backend setup

### Accept `GET` and `POST`

The adapters use `POST` for partial reloads so table state does not fill the URL query string. Register the route with
the package macro:

```php
use Illuminate\Support\Facades\Route;

Route::getAndPost('/users', UsersController::class)
    ->name('users.index');
```

This registers `HEAD`, `GET`, and `POST` for the same action. Apply the same authentication, authorization, and rate
limiting middleware you would use for a normal data endpoint.

### Return paginated data

```php
use App\Models\User;
use Inertia\Inertia;

return Inertia::render('Users/Index', [
    'users' => fn () => User::query()->primevueData(),
]);
```

The builder macro accepts three arguments:

```php
primevueData(
    pageName: 'page',
    globalFilterColumns: null,
    mapOrResource: null,
)
```

- `pageName` isolates pagination and adapter options. Use a unique value for each dataset on a page.
- `globalFilterColumns` limits the columns searched by the global filter. When `null`, all model table columns are
  discovered through the schema.
- `mapOrResource` transforms page items with a closure or a Laravel JSON resource class.

An explicit global search list is usually faster and avoids searching sensitive or unsuitable columns:

```php
'users' => fn () => User::query()->primevueData(
    globalFilterColumns: ['name', 'email'],
),
```

Transform results without losing paginator metadata:

```php
use App\Http\Resources\UserResource;

'users' => fn () => User::query()->primevueData(
    mapOrResource: UserResource::class,
),
```

### Deferred data

Use an Inertia deferred prop when the page shell should render before the query:

```php
'users' => Inertia::defer(
    fn () => User::query()->primevueData(),
),
```

For two adapters, give each one a distinct prop and page name:

```php
return Inertia::render('Dashboard', [
    'users' => Inertia::defer(
        fn () => User::query()->primevueData(pageName: 'users_page'),
    ),
    'categories' => Inertia::defer(
        fn () => Category::query()->primevueData(pageName: 'categories_page'),
    ),
]);
```

## Supported filters

The backend understands PrimeVue filter operators `and` and `or`, plus these match modes:

| Match mode | Behavior |
|---|---|
| `startsWith` | Case-insensitive text prefix. |
| `contains` | Case-insensitive text search. |
| `notContains` | Case-insensitive negative text search. |
| `endsWith` | Case-insensitive text suffix. |
| `equals` | Database equality. |
| `notEquals` | Database inequality. |

Empty constraints and unknown match modes are ignored. Global filtering uses the first valid global constraint and
joins the configured columns with `OR`.

## DataTable

Create the PrimeVue filter state, then connect every adapter callback and value to the matching table prop:

```vue
<script setup lang="ts">
import { ref } from 'vue';
import Button from 'primevue/button';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import { FilterMatchMode, FilterOperator } from '@primevue/core/api';
import { DataTableAdapter } from 'laraveltoolkit';

const filters = ref({
    global: {
        operator: FilterOperator.AND,
        constraints: [{ value: '', matchMode: FilterMatchMode.CONTAINS }],
    },
    name: {
        operator: FilterOperator.AND,
        constraints: [{ value: '', matchMode: FilterMatchMode.CONTAINS }],
    },
    email: {
        operator: FilterOperator.AND,
        constraints: [{ value: '', matchMode: FilterMatchMode.CONTAINS }],
    },
});
</script>

<template>
    <DataTableAdapter
        v-slot="table"
        prop-name="users"
        :rows="15"
        :filters="filters"
        :preserve-state="true"
    >
        <div>
            <input
                v-model="filters.global.constraints[0].value"
                type="search"
                @input="table.manualFilter(filters)"
            >
            <Button label="Clear filters" @click="table.clearFilters" />
        </div>

        <DataTable
            v-model:filters="filters"
            data-key="id"
            filter-display="menu"
            lazy
            paginator
            removable-sort
            sort-mode="multiple"
            :first="table.first"
            :loading="table.loading"
            :rows="table.rows"
            :sort-field="table.currentSort[0]?.field"
            :sort-order="table.currentSort[0]?.order"
            :total-records="table.total"
            :value="table.value"
            @filter="table.filter"
            @page="table.page"
            @sort="table.sort"
        >
            <Column field="id" header="ID" sortable />
            <Column field="name" header="Name" sortable>
                <template #filter="{ filterModel, filterCallback }">
                    <input v-model="filterModel.value" @input="filterCallback()">
                </template>
            </Column>
            <Column field="email" header="Email" sortable />
        </DataTable>
    </DataTableAdapter>
</template>
```

Required connections are `@page`, `@sort`, `@filter`, `loading`, `rows`, `total-records`, and `value`. When preserving
state, also connect `first`, `sort-field`, and `sort-order` as shown.

Useful adapter props:

| Prop | Purpose |
|---|---|
| `propName` | Required Inertia prop containing the paginator. |
| `rows` | Records requested per page. |
| `filters` | PrimeVue filter state. |
| `manualFilterDebounceWait` | Debounce delay for `manualFilter()`. |
| `globalFilterName` | Key used for the global filter; defaults to `global`. |
| `preserveState` | Preserves page, sorting, and filters across visits. |

## DataView

`DataViewAdapter` uses the same backend response and a smaller set of bindings:

```vue
<script setup lang="ts">
import { ref } from 'vue';
import DataView from 'primevue/dataview';
import { DataViewAdapter } from 'laraveltoolkit';

const filters = ref({
    global: { value: '', matchMode: 'contains' },
});
</script>

<template>
    <DataViewAdapter
        v-slot="view"
        prop-name="users"
        :rows="12"
        :filters="filters"
    >
        <input v-model="filters.global.value" type="search">

        <DataView
            data-key="id"
            lazy
            paginator
            :rows="view.rows"
            :total-records="view.total"
            :value="view.value"
            @page="view.page"
        >
            <template #list="{ items }">
                <article v-for="user in items" :key="user.id">
                    <strong>{{ user.name }}</strong>
                    <span>{{ user.email }}</span>
                </article>
            </template>
        </DataView>
    </DataViewAdapter>
</template>
```

The required DataView connections are `@page`, `rows`, `total-records`, and `value`. Adapter props also support
`filterDebounceWait`, `globalFilterName`, `sortField`, and `sortOrder`.

## Joins and aliases

For joined queries, select every filterable/sortable column explicitly and alias computed fields. The backend maps a
frontend field to a selected qualified column or alias where possible:

```php
Product::query()
    ->select('products.*', 'users.name as user_name')
    ->join('users', 'users.id', '=', 'products.user_id')
    ->primevueData(globalFilterColumns: ['products.name', 'users.name']);
```

Keep adapter field names controlled by your own component definitions. Do not pass arbitrary user-provided field
names into query construction outside the adapter contract.

---

[Back to the documentation index](README.md)
