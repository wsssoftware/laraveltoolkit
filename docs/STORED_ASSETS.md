### Stored Assets

After all you must configure the models that will use stored assets feature.

First publish migration:

```bash
php artisan vendor:publish --tag=laraveltoolkit-migrations
```

and run it

```bash
php artisan migrate
```

Then generate model/field store recipe:

```bash
php artisian make:store-recipe ProductImageRecipe
```

After configure it:

```php
/**
     * @inheritDoc
     */
    protected function prepareForSave(AssetIntent $baseAsset): AssetIntent|Collection
    {
//        return collect([
//            $baseAsset->withDisk('local')->withKey('default')->withFilenameStoreType(FilenameStoreType::KEY),
//            AssetIntent::create($baseAsset->pathname)->withDisk('local')
//                ->withFilenameStoreType(FilenameStoreType::KEY)
//                ->withKey('thumbnail'),
//        ]); or

        return $baseAsset->withDisk('local')->withKey('default');
    }
```

Here you can create thumbs, format file or images and more. You must return an `AssetIntent` or a collection of it.

On model add stored trait and put recipe to cast asset field:

```php
use App\StoreRecipes\ProductImageRecipe;
use Illuminate\Database\Eloquent\Model;
use Laraveltoolkit\StoredAssets\HasStoredAssets;

class Product extends Model
{
    // Add trait
    use HasStoredAssets;

    //..

/**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            //...
            'image' => ProductImageRecipe::class,
            //...
        ];
    }
}
```

That is all. Now you can save an asset just passing an UploadedFile, a File, or a path.

For handling with model rows, you must configure a job that will move files for trash and some days after delete it:

```php
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Laraveltoolkit\StoredAssets\Jobs\GarbageCollectorManager;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();


Schedule::job(GarbageCollectorManager::class)
    ->everySixHours();
```
