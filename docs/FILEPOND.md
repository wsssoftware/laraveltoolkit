# FilePond uploads

The FilePond module stores browser uploads in a temporary filesystem location and returns a UUID to the form. Before
validation, the request macro converts that UUID into a Laravel-compatible `UploadedFile`.

It supports normal uploads, chunked uploads, revert, restore, load, and remote fetch through package routes under
`/lt/filepond`.

## Setup

Publish the package configuration:

```bash
php artisan vendor:publish --tag=laraveltoolkit-config
```

Configure the temporary disk and path with environment variables:

```dotenv
LT_FILEPOND_DISK=local
LT_FILEPOND_ROOT_PATH=lt_filepond
LT_FILEPOND_GC_PROBABILITY=0.1
LT_FILEPOND_GC_UPLOAD_LIFE=86400
LT_FILEPOND_GC_MAXIMUM_INTERACTIONS=100
```

The disk must provide a local path because the converted upload is backed by a filesystem file. Keep temporary
uploads on a private disk; they are not permanent application assets.

| Setting | Meaning |
|---|---|
| `disk` | Laravel filesystem disk used for temporary uploads. |
| `root_path` | Base directory inside that disk. |
| `garbage_collector.probability` | Chance from `0.0` to `1.0` that cleanup runs after an upload. |
| `garbage_collector.upload_life` | Minimum age in seconds before a temporary upload may be removed. |
| `garbage_collector.maximum_interactions` | Maximum entries processed by one cleanup run. |

## Frontend

The companion Vuetoolkit package provides a preconfigured `FilepondInput`:

```vue
<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { FilepondInput } from 'laraveltoolkit';

const form = useForm<{ attachment: string | null }>({
    attachment: null,
});
</script>

<template>
    <form @submit.prevent="form.post('/documents')">
        <FilepondInput
            id="attachment"
            v-model="form.attachment"
            :form="form"
            :chunk="true"
            required
        />

        <button type="submit" :disabled="form.processing">Save</button>
    </form>
</template>
```

After FilePond finishes uploading, `form.attachment` contains the temporary upload UUID, not the binary file.

## Convert and validate the upload

Call `mergeFilepond()` in `prepareForValidation()` so Laravel's file rules receive an uploaded file:

```php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Laraveltoolkit\Filepond\UploadedFile;

/** @property-read UploadedFile|null $attachment */
final class StoreDocumentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'attachment' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->mergeFilepond('attachment');
    }
}
```

You can also resolve the value without mutating the request:

```php
$file = $request->filepond('attachment');
```

Both methods return `null` when the identifier is absent, malformed, or does not point to exactly one temporary file.

## Persisting the file

After validation, treat the value like a Laravel uploaded file:

```php
$path = $request->attachment->store('documents', 'private');
```

Or assign it directly to a model field configured with a [Stored Assets recipe](STORED_ASSETS.md).

Temporary data is deleted after the request when validation did not place errors in the session. Persist or move the
file during the request; do not keep the temporary UUID as your application's permanent file reference.

## Routes

The service provider registers these named routes with Laravel's `web` middleware:

| Method | Path | Route name |
|---|---|---|
| `POST` | `/lt/filepond/process` | `lt.filepond.process` |
| `PATCH` | `/lt/filepond/process-chunk` | `lt.filepond.process_chunk` |
| `DELETE` | `/lt/filepond/revert` | `lt.filepond.revert` |
| `GET` | `/lt/filepond/load` | `lt.filepond.load` |
| `GET` | `/lt/filepond/restore` | `lt.filepond.restore` |
| `GET` | `/lt/filepond/fetch` | `lt.filepond.fetch` |

> [!CAUTION]
> The `load` and `fetch` endpoints retrieve a URL supplied by the client. Do not expose remote-file loading to
> untrusted users unless your application validates destinations or restricts access at the web server/application
> boundary. This avoids turning the endpoint into a server-side request forgery surface.

## Troubleshooting

- If conversion returns `null`, confirm the submitted value is the UUID returned by the process endpoint and that the
  temporary directory contains exactly one completed file.
- If chunked uploads never complete, verify that the proxy forwards the `Upload-Length`, `Upload-Offset`, and
  `Upload-Name` headers and permits `PATCH` requests.
- If file validation reports an unreadable upload, use a local-compatible filesystem disk and confirm the PHP process
  can read and rename its files.

---

[Back to the documentation index](README.md)
