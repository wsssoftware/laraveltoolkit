## PrimeVue Data

A minimalist implementation of DataTables and DataView on Laravel

### Frontend (Laravel)

On laravel, basically, you need to do only two tings.

#### Change route method

```php
// From this
Route::get('/', Controller::class);

// To this
Route::getAndPost('/', Controller::class);
```

> To avoid polluting the URLs with search and sorting parameters, we send the parameters via post in the Inertia`reload`
> method.

#### Config action `data` prop

```php
use \App\Models\User;
use \Inertia\Inertia;

Inertia::render('Users', [
    'users' => fn() => User::query()->primevueData(),
])
```

> This way, the data from the current pagination page will be loaded together with the first load.

With Lazy load:

```php
use \App\Models\User;
use \Inertia\Inertia;

Inertia::render('Users', [
    'users' => Inertia::lazy(fn() => User::query()->primevueData()),
])
```

> Here, the main page is loaded first, and only then does it load the table data automatically.

With two `data`:

```php
use \App\Models\User;
use \Inertia\Inertia;

Inertia::render('Users', [
    'users' => Inertia::lazy(fn() => User::query()->primevueData()),
    'categories' => Inertia::lazy(fn() => Categories::query()->primevueData('page_categories')),
])
```

> If you do not change the `pageName` attribute when using more than one `data`, some unexpected behavior may occur.

With custom global `$globalFilterColumn` attribute:

```php
use \App\Models\User;
use \Inertia\Inertia;

Inertia::render('Users', [
    'users' => Inertia::lazy(fn() => User::query()->primevueData(globalFilterColumn: 'foo')),
])
```

> In some scenarios, such as when the database table in question has a column called `global`, it may be necessary to
> change this attribute so that it is possible to search all columns in the table.

### Frontend (PrimeVue)

## DataTable

An example of how use with DataTable.

Some points of `attention` are:

- Remember to put lazy prop on DataTable.
- You must create filter data prop to use the filters function. Remember to pass it on DataTable.

Available adapter props:

- **manualFilterDebounceWait**: How many milliseconds filter will debounce before do it.
- **globalFilterName**: Here you configure if needed the global filter key. On this filter, it will search all (or
  configured in Laravel
- **propName (required)**: Prop that will be load. Configured by you on Inertia props on Laravel. Eg.: users.
- **rows**: How many rows per page adapter will get.
- **preserveState**: If true, will preserve sort, filter and page state.

> If you want to use preserveState, you must set `first`, `sortField` and `sortOrder` like bellow to work correctly.

```vue

<template>
    <DataTableAdapter prop-name="users" v-slot="props" :rows="15" :filters="filters">
        <Button label="Clear" @click="props.clearFilters"/>
        <input v-model="filters.global.constraints[0].value" @keyup="props.manualFilter(filters)">
        <DataTable
            :first="props.first"
            :sort-field="props.currentSort[0]?.field"
            :sort-order="props.currentSort[0]?.order"
            removable-sort
            sort-mode="multiple"
            data-key="id"
            @page="props.page"
            @filter="props.filter"
            filter-display="menu"
            @sort="props.sort"
            v-model:filters="filters"
            :loading="props.loading"
            lazy
            :rows="props.rows"
            paginator
            :total-records="props.total"
            :value="props.value">
            <Column field="id" :sortable="true" header="Id">
                <template #filter="{filterModel, filterCallback}">
                    <input v-model="filterModel.value" type="text" @input="filterCallback()" class="p-column-filter"
                           placeholder="Search by country"/>
                </template>
            </Column>
            <Column field="name" :sortable="true" header="Nome">
                <template #filter="{filterModel, filterCallback}">
                    <input v-model="filterModel.value" type="text" @input="filterCallback()" class="p-column-filter"
                           placeholder="Search by country"/>
                </template>
            </Column>
            <Column field="email" :sortable="true" header="Email"/>
        </DataTable>
    </DataTableAdapter>
</template>

<script lang="ts">
    import {defineComponent} from "vue";
    import {DataTableAdapter} from "laraveltoolkit";
    import DataTable from "primevue/datatable";
    import Column from "primevue/column";
    import Button from "primevue/button";
    import {FilterMatchMode, FilterOperator} from '@primevue/core/api';

    export default defineComponent({
        name: "TableAdapter",
        components: {
            Button,
            DataTableAdapter,
            DataTable,
            Column
        },
        data() {
            return {
                filters: {
                    global: {operator: FilterOperator.AND, constraints: [{value: '', matchMode: 'contains'}]},
                    id: {operator: FilterOperator.AND, constraints: [{value: '', matchMode: FilterMatchMode.CONTAINS}]},
                    name: {
                        operator: FilterOperator.AND,
                        constraints: [{value: '', matchMode: FilterMatchMode.CONTAINS}]
                    },
                },
            }
        },
    });
</script>
```

> Some props are required that you pass to `DataTable` to get all features from adapter.
>
> - **page**: You must pass to `@page` event to handle page changes.
> - **sort**: You must pass to `@sort` event to handle sort changes.
> - **filter**: You must pass to `@filter` event to handle filter changes.
> - **loading**: You must pass to `loading` prop to handle loading fallback.
> - **rows**: You must pass to `rows` prop to work.
> - **total**: You must pass to `total-records` prop to work.
> - **value**: You must pass to `value` prop to work.

## DataView

An example of how use with DataView.

Some points of `attention` are:

- Remember to put lazy prop on DataView.
- You must create filter data prop to use the filters function. Remember to pass it on DataViewAdapter and DataView.

Available adapter props:

- **filters**: Here you will pass your filters like you pass to DataView.
- **filterDebounceWait**: How many milliseconds filter will debounce before do it.
- **globalFilterName**: Here you configure if needed the global filter key. On this filter, it will search all (or
  configured in Laravel
- **propName (required)**: Prop that will be load. Configured by you on Inertia props on Laravel. Eg.: users.
- **rows**: How many rows per page adapter will get.
- **sortField**: Table field that will be sorted.
- **sortOrder**: Sort direction (asc or desc).

```vue

<template>
    <DataViewAdapter prop-name="users" v-slot="props" :rows="5" :filters="filters">
        <Button label="Clear" @click="props.clearFilters"/>
        <input v-model="filters.global.value">
        <DataView
            data-key="id"
            @page="props.page"
            lazy
            :rows="props.rows"
            paginator
            :total-records="props.total"
            :value="props.value">
            <template #list="slotProps">
                <div class="flex flex-col">
                    <div v-for="(item, index) in slotProps.items" :key="index">
                        <div class="flex flex-col sm:flex-row sm:items-center p-6 gap-4"
                             :class="{ 'border-t border-surface-200 dark:border-surface-700': index !== 0 }">
                            <div class="flex flex-col md:flex-row justify-between md:items-center flex-1 gap-6">
                                <div class="flex flex-row md:flex-col justify-between items-start gap-2">
                                    <div>
                                        <span class="font-medium text-surface-500 dark:text-surface-400 text-sm">{{ item.category }}</span>
                                        <div class="text-lg font-medium mt-2">{{ item.name }}</div>
                                    </div>
                                    <div class="bg-surface-100 p-1" style="border-radius: 30px">
                                        <div class="bg-surface-0 flex items-center gap-2 justify-center py-1 px-2">
                                            <span class="text-surface-900 font-medium text-sm">{{ item.email }}</span>
                                            <i class="pi pi-star-fill text-yellow-500"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex flex-col md:items-end gap-8">
                                    <span class="text-xl font-semibold">{{ item.id }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </DataView>
    </DataViewAdapter>
</template>

<script lang="ts">
    import {defineComponent} from "vue";
    import {DataViewAdapter} from "laraveltoolkit";
    import DataView from "primevue/dataview";
    import Button from "primevue/button";

    export default defineComponent({
        name: "ViewAdapter",
        components: {
            Button,
            DataViewAdapter,
            DataView,
        },
        data() {
            return {
                filters: {
                    global: {value: '', matchMode: 'contains'},
                    id: {value: '', matchMode: 'contains'},
                    name: {value: '', matchMode: 'contains'}
                },
            }
        },
        mounted() {
        }
    });
</script>
```

> Some props are required that you pass to `DataView` to get all features from adapter.
>
> - **page**: You must pass to `@page` event to handle page changes.
> - **rows**: You must pass to `rows` prop to work.
> - **total**: You must pass to `total-records` prop to work.
> - **value**: You must pass to `value` prop to work.
