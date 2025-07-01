### FilePond

On vue, use the package component to help you use:

```vue
<template>
    <form @submit.prevent="submit" class="w-full bg-white px-3 py-2 rounded shadow flex flex-col">
        <Title>Upload test</Title>
        <InputGroup :form="form" field="normal" label="Normal" v-slot="attrs">
            <FilepondInput
                :chunk="true"
                :disabled="attrs.disabled"
                :form="form"
                :id="attrs.id"
                v-model="form.normal"
                :required="attrs.required"
                :invalid="attrs.invalid"
                :aria-describedby="attrs['aria-describedby']"/>
        </InputGroup>
    </form>
</template>

<script lang="ts">
import {defineComponent} from "vue";
import {FilepondInput, InputGroup} from "laraveltoolkit";
import {useForm} from "@inertiajs/vue3";

export default defineComponent({
    name: "UploadTest" ,
    components: {
        FilepondInput,
        InputGroup,
    },
    data() {
        return {
            form: useForm({
                normal: null,
            }),
        }
    },
    methods: {
        submit(): void {
            //submit
        }
    }
});
</script>
```

Filepond will upload the file and put a uuid fot that file on input.

On request you must call `mergeFilepond` to convert the uploaded uuid into Uploaded file. After this you can validate it
as a normal upload.

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property \Laraveltoolkit\Filepond\UploadedFile $normal
 */
class FilepondRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'normal' => 'required|extensions:zip',
        ];
    }

    public function prepareForValidation(): void
    {
        $this->mergeFilepond('normal');
    }
}
```
