# Stored Assets

Stored Assets ties durable files to Eloquent attributes through recipes. A recipe can store one source file as one or
more named variants, choose disks and visibility, and return rich `Asset` objects from the model. A queued garbage
collector moves unreferenced asset directories to a recoverable trash bin before permanent deletion.

## Setup

### 1. Publish migrations and configuration

```bash
php artisan vendor:publish --tag=laraveltoolkit-migrations
php artisan vendor:publish --tag=laraveltoolkit-config
php artisan migrate
```

The package migration creates `stored_assets`, which records each asset set. Your application model also needs a
nullable UUID column for every stored-asset field:

```php
Schema::table('products', function (Blueprint $table) {
    $table->storedAsset('image')->nullable();
});
```

`storedAsset()` is a package schema macro equivalent to an indexed UUID column.

### 2. Generate a recipe

```bash
php artisan make:store-recipe ProductImageRecipe
```

Configure the generated recipe by returning one `AssetIntent`:

```php
namespace App\StoreRecipes;

use Illuminate\Support\Collection;
use Laraveltoolkit\StoredAssets\AssetIntent;
use Laraveltoolkit\StoredAssets\FilenameStoreType;
use Laraveltoolkit\StoredAssets\Recipe;

final class ProductImageRecipe extends Recipe
{
    protected function prepareForSave(AssetIntent $baseAsset): AssetIntent|Collection
    {
        return $baseAsset
            ->withDisk('public')
            ->withKey('default')
            ->withFilenameStoreType(FilenameStoreType::KEY)
            ->asPublic();
    }
}
```

The source is available as `$baseAsset->pathname`, so the recipe is the right place to optimize an image, generate a
thumbnail, convert a document, or inspect metadata before storage.

### 3. Configure the model

Add the trait and cast the UUID column to your recipe:

```php
use App\StoreRecipes\ProductImageRecipe;
use Illuminate\Database\Eloquent\Model;
use Laraveltoolkit\StoredAssets\HasStoredAssets;

final class Product extends Model
{
    use HasStoredAssets;

    protected function casts(): array
    {
        return [
            'image' => ProductImageRecipe::class,
        ];
    }
}
```

The recipe cast and database column must use the same field name.

## Store and read an asset

Assign an uploaded file, an `Illuminate\Http\File`, a local pathname, an existing stored-asset UUID, or `null`:

```php
$product->image = $request->file('image');
$product->save();

$product->image_uuid;          // UUID stored in products.image
$product->image->default;      // Laraveltoolkit\StoredAssets\Asset
$product->image->default->url();
$product->image->default->get();
$product->image->default->readStream();
$product->image->default->temporaryUrl(now()->addMinutes(10));
```

File recipes run during the model's `saving` event. Saving creates the physical assets and a `stored_assets` record,
then writes its UUID to the model column.

When reusing an existing UUID, the referenced stored-asset record must exist. The package rejects arbitrary or stale
UUIDs.

## Multiple variants

Return a collection of intents with unique keys:

```php
protected function prepareForSave(AssetIntent $baseAsset): AssetIntent|Collection
{
    $thumbnailPath = $this->createThumbnail($baseAsset->pathname);

    return collect([
        $baseAsset
            ->withDisk('public')
            ->withKey('original')
            ->withFilenameStoreType(FilenameStoreType::KEY)
            ->asPublic(),

        AssetIntent::create($thumbnailPath)
            ->withDisk('public')
            ->withKey('thumbnail')
            ->withFilenameStoreType(FilenameStoreType::KEY)
            ->asPublic(),
    ]);
}
```

Read variants by key:

```php
$product->image->original->url();
$product->image->thumbnail->url();
```

Keys must be valid PHP-like identifiers and unique within a recipe result. `FilenameStoreType::KEY` uses the key as
the filename; `FilenameStoreType::UUID` generates a UUID filename.

## Creating intents

Recipes usually receive the base intent, but custom variants can be created from other sources:

```php
AssetIntent::create($pathname);
AssetIntent::createFromUploadedFile($uploadedFile);
AssetIntent::createFromContent($generatedContents);
AssetIntent::createFromResource($stream);
```

Intent options are chainable:

```php
$intent
    ->withDisk('s3')
    ->withKey('download')
    ->withFilenameStoreType(FilenameStoreType::UUID)
    ->withOptions(['CacheControl' => 'max-age=31536000'])
    ->asPrivate();
```

`asPublic()` adds public visibility to the filesystem write; private is the default.

## Garbage collection

Deleting a model or replacing an asset does not synchronously delete files. Schedule the manager and run a queue
worker:

```php
use Illuminate\Support\Facades\Schedule;
use Laraveltoolkit\StoredAssets\Jobs\GarbageCollectorManager;

Schedule::job(new GarbageCollectorManager)
    ->everySixHours()
    ->withoutOverlapping();
```

The manager:

1. scans configured asset directories for UUIDs no longer referenced by their model field;
2. moves orphaned directories to the trash bin;
3. permanently removes trash entries after the configured deadline;
4. dispatches a summary job.

Pass disk names when assets are spread across multiple disks:

```php
new GarbageCollectorManager(['local', 'public', 's3']);
```

Relevant configuration:

| Key | Purpose |
|---|---|
| `stored_assets.disk` | Default filesystem disk. |
| `stored_assets.model` | Replaceable stored-asset Eloquent model. |
| `stored_assets.path` | Base directory for assets. |
| `stored_assets.filename_store_type` | Default `KEY` or `UUID` filename strategy. |
| `stored_assets.trash_bin.folder` | Trash directory inside the base path. |
| `stored_assets.trash_bin.deadline` | Minutes an orphan remains recoverable before deletion. |
| `stored_assets.trash_bin_cleaner_timeout` | Seconds a cleanup job may work before continuing in a new job. |

> [!IMPORTANT]
> Keep queue workers running and schedule the manager. Without both, orphaned assets remain on disk indefinitely.

---

[Back to the documentation index](README.md)
