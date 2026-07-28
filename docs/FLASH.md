# Flash messages

The Flash module sends structured messages through Inertia's flash data. The companion Vuetoolkit receiver turns
them into PrimeVue toasts after a redirect or Inertia response.

## Sending messages

```php
use Laraveltoolkit\Facades\Flash;

Flash::success('User saved.');
Flash::info('The import is still running.');
Flash::warn('Your subscription expires soon.');
Flash::error('The report could not be generated.');
Flash::secondary('A neutral update.');
Flash::contrast('An emphasized update.');
```

The first argument is the detail. The optional second argument is a summary:

```php
Flash::success('The profile changes are now active.', 'Profile saved');
```

Messages are chainable:

```php
Flash::success('User saved.')
    ->closable()
    ->withLife(4_000)
    ->withGroup('account');

Flash::error('Connection lost.')->unclosable();
```

`life` follows PrimeVue and is expressed in milliseconds.

## Defaults

Publish the package configuration to set defaults for every message:

```bash
php artisan vendor:publish --tag=laraveltoolkit-config
```

```php
'flash' => [
    'defaults' => [
        'closable' => null,
        'life' => null,
        'group' => 'lt-default',
    ],
],
```

A `null` option is omitted from the flash payload and lets PrimeVue apply its own default. Chain methods override the
configured value for one message.

## PrimeVue receiver

First install and configure PrimeVue's Toast service as described in the
[PrimeVue Toast documentation](https://primevue.org/toast/). Mount one `Toast` for each group you use, then initialize
Vuetoolkit's receiver once in a persistent layout:

```vue
<script lang="ts">
import { defineComponent } from 'vue';
import Toast from 'primevue/toast';
import { ToastReceiver } from 'laraveltoolkit';

export default defineComponent({
    components: { Toast },
    created() {
        ToastReceiver(this.$toast);
    },
});
</script>

<template>
    <Toast group="lt-default" />
    <Toast group="account" />
    <slot />
</template>
```

Keep the receiver in a layout that is not repeatedly mounted during normal Inertia navigation; otherwise the same
message may be handled more than once.

## Testing

The facade includes PHPUnit/Pest-compatible assertions:

```php
use Laraveltoolkit\Facades\Flash;
use Laraveltoolkit\Flash\Severity;

Flash::assertFlashed();
Flash::assertFlashed(Severity::SUCCESS);
Flash::assertFlashed(Severity::SUCCESS, 'User saved.');
Flash::assertFlashed(Severity::SUCCESS, 2);

Flash::assertNotFlashed();
Flash::assertNotFlashed(Severity::ERROR);
```

The second `assertFlashed()` argument matches an exact detail when it is a string, or an exact message count when it
is an integer.

---

[Back to the documentation index](README.md)
