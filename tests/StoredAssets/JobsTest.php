<?php

use Illuminate\Http\UploadedFile;
use Laraveltoolkit\Facades\StoredAssets;
use Laraveltoolkit\StoredAssets\Jobs\GarbageCollectorManager;
use Laraveltoolkit\StoredAssets\Jobs\TrashBinCleaner;
use Laraveltoolkit\Tests\Model\Product;

it('can run all jobs', function () {
    $disk = Storage::fake('local');

    $validFakeTrashBinUuid = StoredAssets::trashBinDeadlineTimestamp().'-'.Str::uuid()->toString();
    $validFakeTrashBinPath = StoredAssets::trashBinPath($validFakeTrashBinUuid);
    $validFakeTrashBinPathname = $validFakeTrashBinPath.'test.txt';
    $disk->put($validFakeTrashBinPathname, '');

    $invalidFakeTrashBinUuid = StoredAssets::trashBinDeadlineTimestamp(now()->subMonth()).'-'.Str::uuid()->toString();
    $invalidFakeTrashBinPath = StoredAssets::trashBinPath($invalidFakeTrashBinUuid);
    $invalidFakeTrashBinPathname = $invalidFakeTrashBinPath.'test.txt';
    $disk->put($invalidFakeTrashBinPathname, '');

    Product::create([
        'id' => 1,
        'image' => UploadedFile::fake()->image('image.jpg'),
    ]);
    Product::create([
        'id' => 2,
        'image' => UploadedFile::fake()->image('image.jpg'),
    ]);
    Product::create([
        'id' => 3,
        'image' => UploadedFile::fake()->image('image.jpg'),
    ]);
    $product = Product::create([
        'id' => 4,
        'image' => UploadedFile::fake()->image('image.jpg'),
    ]);
    $uuid = $product->image_uuid;
    $assetPath = StoredAssets::path($uuid);
    $assetSubDir1 = $assetPath.'../';
    $assetSubDir2 = $assetSubDir1.'../';
    $product->delete();

    expect($disk->exists($assetPath))->toBeTrue()
        ->and($disk->exists($assetSubDir1))->toBeTrue()
        ->and($disk->exists($assetSubDir2))->toBeTrue()
        ->and($disk->exists($validFakeTrashBinPathname))->toBeTrue()
        ->and($disk->exists($invalidFakeTrashBinPathname))->toBeTrue();

    GarbageCollectorManager::dispatch();

    expect($disk->exists($assetPath))->toBeFalse()
        ->and($disk->exists($assetSubDir1))->toBeTrue()
        ->and($disk->exists($assetSubDir2))->toBeTrue()
        ->and($disk->exists($validFakeTrashBinPath))->toBeTrue()
        ->and($disk->exists($invalidFakeTrashBinPathname))->toBeFalse();
    GarbageCollectorManager::dispatch();

    expect($disk->exists($assetPath))->toBeFalse()
        ->and($disk->exists($assetSubDir1))->toBeFalse()
        ->and($disk->exists($assetSubDir2))->toBeTrue();
    GarbageCollectorManager::dispatch();

    expect($disk->exists($assetPath))->toBeFalse()
        ->and($disk->exists($assetSubDir1))->toBeFalse()
        ->and($disk->exists($assetSubDir2))->toBeFalse();
});

it('can break after long garbage run', function () {
    $this->withoutDefer();
    Queue::fake([TrashBinCleaner::class]);
    $disk = Storage::fake('local');

    for ($i = 0; $i < 10; $i++) {
        $invalidFakeTrashBinUuid = StoredAssets::trashBinDeadlineTimestamp(now()->subMonth()).'-'.Str::uuid()->toString();
        $invalidFakeTrashBinPath = StoredAssets::trashBinPath($invalidFakeTrashBinUuid);
        $invalidFakeTrashBinPathname = $invalidFakeTrashBinPath.'test.txt';
        $disk->put($invalidFakeTrashBinPathname, '');
    }
    config()->set('laraveltoolkit.stored_assets.trash_bin_cleaner_timeout', -1);

    new TrashBinCleaner('local')->handle();
    Queue::assertPushed(TrashBinCleaner::class);

});
