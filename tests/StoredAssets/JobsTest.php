<?php

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Laraveltoolkit\Facades\StoredAssets;
use Laraveltoolkit\StoredAssets\Jobs\GarbageCollector;
use Laraveltoolkit\StoredAssets\Jobs\GarbageCollectorManager;
use Laraveltoolkit\StoredAssets\Jobs\TrashBinCleaner;
use Laraveltoolkit\StoredAssets\StoredAssetModel;
use Laraveltoolkit\Tests\Model\Product;

it('uses the optimized suffix lookup on MySQL', function () {
    $disk = Storage::fake('local');
    $uuid = Str::uuid()->toString();
    $assetPath = StoredAssets::path($uuid, 'test.txt');
    $disk->put($assetPath, 'content');

    $connection = Mockery::mock(ConnectionInterface::class);
    $connection->shouldReceive('getDriverName')->once()->andReturn('mysql');

    $model = Mockery::mock(StoredAssetModel::class)->makePartial();
    $model->shouldReceive('getConnection')->once()->andReturn($connection);

    $query = Mockery::mock(Builder::class);
    $query->shouldReceive('where')->once()->with('id_suffix', substr($uuid, -4))->andReturnSelf();
    $query->shouldReceive('selectRaw')->once()->andReturnSelf();
    $query->shouldReceive('groupBy')->once()->with('field_model')->andReturnSelf();
    $query->shouldReceive('get')->once()->andReturn(collect());

    $originalStoredAssets = StoredAssets::getFacadeRoot();

    try {
        $storedAssets = StoredAssets::partialMock();
        $storedAssets->shouldReceive('newModel')->once()->with([])->andReturn($model);
        $storedAssets->shouldReceive('modelQuery')->once()->andReturn($query);
        $storedAssets->shouldReceive('moveToTrashBin')->once()->with('local', $uuid)->andReturnFalse();

        new GarbageCollector('local', dirname($assetPath, 3))->handle();
    } finally {
        StoredAssets::swap($originalStoredAssets);
    }
});

it('can run all jobs', function () {
    config()->set('laraveltoolkit.stored_assets.trash_bin_cleaner_timeout', 55);
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
    $assetSubDir1 = str($assetPath)->beforeLast('/')->beforeLast('/')->append('/')->toString();
    $assetSubDir2 = str($assetSubDir1)->beforeLast('/')->beforeLast('/')->append('/')->toString();
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
    $assetSubDir2HasOtherSubDirectories = $disk->directories($assetSubDir2) !== [];
    GarbageCollectorManager::dispatch();

    expect($disk->exists($assetPath))->toBeFalse()
        ->and($disk->exists($assetSubDir1))->toBeFalse()
        ->and($disk->exists($assetSubDir2))->toBe($assetSubDir2HasOtherSubDirectories);
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
