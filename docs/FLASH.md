### Flash

Flash help you send messages for UI.

```php
use Laraveltoolkit\Facades\Flash;
use Laraveltoolkit\Flash\Severity;

// You can use this severities
Flash::success('Success Test');
Flash::info('Info Test');
Flash::warn('Warn Test');
Flash::error('Error Test');
Flash::secondary('Secondary Test');
Flash::contrast('Contrast Test');

// You can pass a summary
Flash::success('Message detail', 'Success!');

// You can mark flash as closable or unclosable
Flash::success('Success Test closable')->closable();
Flash::success('Success Test unclosable')->unclosable();

// You can pass a lifetime in seconds to your flash
Flash::success('Success Test with life')->withLife(2000);

// Your message can belong to another group (renders in another primevue group)
Flash::success('Success Test in other group')->withGroup('foo_bar');

// You can test flash using
 Flash::assertFlashed(Severity::SUCCESS, 'User saved!');
 Flash::assertNotFlashed(Severity::SUCCESS, 'User saved!');
```

You must first configure primevue Toast service following [this guide](https://primevue.org/toast/) and then put
`ToastReceiver`
Composable on created Vue method to init it.

```vue

<template>
    <div>
        <!-- lt-default is de default group -->
        <Toast group="lt-default"/>
        <slot/>
    </div>
</template>

<script lang="ts">
    import {defineComponent} from "vue";
    import {ToastReceiver} from 'laraveltoolkit';
    import {Toast} from 'primevue';

    export default defineComponent({
        name: "Flash",
        components: {
            Toast,
        },
        created() {
            ToastReceiver(this.$toast)
        },
    });
</script>
```
