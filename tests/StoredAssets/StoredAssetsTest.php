<?php

use Illuminate\Database\Eloquent\Builder;
use Laraveltoolkit\Facades\StoredAssets;
use Laraveltoolkit\StoredAssets\Assets;
use Laraveltoolkit\StoredAssets\FilenameStoreType;
use Laraveltoolkit\StoredAssets\StoredAssetModel;
use Laraveltoolkit\Tests\StoredAssets\MoveDirectoryFilesystemTest;

it('can get base assets', function () {
    $path1 = StoredAssets::basePath();
    config()->set('laraveltoolkit.stored_assets.path', 'foo_bar_assets');
    $path2 = StoredAssets::basePath();

    expect($path1)
        ->toEqual('assets')
        ->and($path2)
        ->toEqual('foo_bar_assets');
});

it('can get default disk', function () {
    $disk1 = StoredAssets::defaultDisk();
    config()->set('laraveltoolkit.stored_assets.disk', 'foo_bar_disk');
    $disk2 = StoredAssets::defaultDisk();

    expect($disk1)
        ->toEqual('local')
        ->and($disk2)
        ->toEqual('foo_bar_disk');
});

it('can get subdirectory chars', function () {
    $chars1 = StoredAssets::subdirectoryChars();
    config()->set('laraveltoolkit.stored_assets.subdirectory_chars', 3);
    $chars2 = StoredAssets::subdirectoryChars();

    expect($chars1)
        ->toEqual(2)
        ->and($chars2)
        ->toEqual(3);
});

it('can get default filename store type', function () {
    $type1 = StoredAssets::defaultFilenameStoreType();
    config()->set('laraveltoolkit.stored_assets.filename_store_type', 'key');
    $type2 = StoredAssets::defaultFilenameStoreType();
    config()->set('laraveltoolkit.stored_assets.filename_store_type', 'uuid');
    $type3 = StoredAssets::defaultFilenameStoreType();
    config()->set('laraveltoolkit.stored_assets.filename_store_type', FilenameStoreType::KEY);
    $type4 = StoredAssets::defaultFilenameStoreType();
    config()->set('laraveltoolkit.stored_assets.filename_store_type', FilenameStoreType::UUID);
    $type5 = StoredAssets::defaultFilenameStoreType();

    expect($type1)
        ->toEqual(FilenameStoreType::UUID)
        ->and($type2)
        ->toEqual(FilenameStoreType::KEY)
        ->and($type3)
        ->toEqual(FilenameStoreType::UUID)
        ->and($type4)
        ->toEqual(FilenameStoreType::KEY)
        ->and($type5)
        ->toEqual(FilenameStoreType::UUID);
});

it('return a model and helpers', function () {
    expect(StoredAssets::newModel())
        ->toBeInstanceOf(StoredAssetModel::class)
        ->and(StoredAssets::modelFQN())
        ->toEqual(StoredAssetModel::class)
        ->and(StoredAssets::modelQuery())
        ->toBeInstanceOf(Builder::class);
});

it('can get the path', function () {
    $uuid = 'fa21cdf7-bb1d-4fc8-a556-67ecd85423d1';
    $expectation = str('assets')
        ->append(DIRECTORY_SEPARATOR)
        ->append('fa')
        ->append(DIRECTORY_SEPARATOR)
        ->append('d1')
        ->append(DIRECTORY_SEPARATOR)
        ->append($uuid)
        ->append(DIRECTORY_SEPARATOR);
    expect(StoredAssets::path($uuid))
        ->toEqual($expectation);
});

it('can get trash bin deadline', function () {
    $timestamp1 = StoredAssets::trashBinDeadlineTimestamp();
    config()->set('laraveltoolkit.stored_assets.trash_bin.deadline', 60 * 24 * 11);
    $timestamp2 = StoredAssets::trashBinDeadlineTimestamp(today()->subDay());

    expect($timestamp1)
        ->toBeInt()
        ->toEqual(now()->addMinutes(60 * 24 * 15)->startOfHour()->getTimestamp())
        ->and($timestamp2)
        ->toBeInt()
        ->toEqual(today()->addMinutes(60 * 24 * 10)->startOfHour()->getTimestamp());
});

it('can check if an uuid is a valid asset', function () {
    $validUuid = Str::uuid()->toString();
    StoredAssets::newModel([
        'id' => $validUuid,
        'model' => 'App\Models\Foobar',
        'field' => 'image',
        'assets' => new Assets([]),
    ])->save();
    expect(StoredAssets::isValidUuidAsset(Str::uuid()->toString()))
        ->toBeFalse()
        ->and(StoredAssets::isValidUuidAsset('Foo_bar'))
        ->toBeFalse()
        ->and(StoredAssets::isValidUuidAsset($validUuid))
        ->toBeTrue();
});

it('can clear trash bin', function () {
    $disk = Storage::fake('local');
    $disk->put(StoredAssets::trashBinPath(Str::uuid()->toString().'/test.txt'), 'foo');
    $disk->put(StoredAssets::trashBinPath(Str::uuid()->toString().'/test.txt'), 'foo');
    $disk->put(StoredAssets::trashBinPath(Str::uuid()->toString().'/test.txt'), 'foo');
    $disk->put(StoredAssets::trashBinPath(Str::uuid()->toString().'/test.txt'), 'foo');

    expect($disk->directories(StoredAssets::trashBinPath()))
        ->toHaveCount(4)
        ->and(StoredAssets::clearTrashBin('local'))
        ->toEqual(4)
        ->and($disk->directories(StoredAssets::trashBinPath()))
        ->toHaveCount(0)
        ->and(StoredAssets::clearTrashBin('local'))
        ->toEqual(0);
});

it('can delete from trash bin', function () {
    $disk = Storage::fake('local');
    $validUuid = Str::uuid()->toString();
    $invalidUuid = Str::uuid()->toString();
    $disk->put(StoredAssets::trashBinPath($validUuid.'/test.txt'), 'foo');

    expect(StoredAssets::deleteFromTrashBin('local', $validUuid))
        ->toBeTrue()
        ->and($disk->exists($validUuid.'/test.txt'))
        ->toBeFalse()
        ->and(StoredAssets::deleteFromTrashBin('local', $invalidUuid))
        ->toBeFalse();
});

it('can move to and restore from trash bin', function () {
    $disk = Storage::fake('local');
    $validUuid = Str::uuid()->toString();
    $originalPath = StoredAssets::path($validUuid, 'test.txt');
    $trashBinPath = StoredAssets::trashBinPath(sprintf('%s-%s', StoredAssets::trashBinDeadlineTimestamp(), $validUuid));
    $disk->put($originalPath, 'foo');

    expect($disk->exists($trashBinPath))
        ->toBeFalse()
        ->and(StoredAssets::moveToTrashBin('local', $validUuid))
        ->toBeTrue()
        ->and($disk->exists($originalPath))
        ->toBeFalse()
        ->and(StoredAssets::moveToTrashBin('local', $validUuid))
        ->toBeFalse()
        ->and($disk->exists($trashBinPath))
        ->toBeTrue()
        ->and(StoredAssets::restoreFromTrashBin('local', $validUuid))
        ->toBeTrue()
        ->and(StoredAssets::restoreFromTrashBin('local', $validUuid))
        ->toBeFalse()
        ->and($disk->exists($trashBinPath))
        ->toBeFalse()
        ->and($disk->exists($originalPath))
        ->toBeTrue();
});

it('test move folder and fail on copy', function () {
    Storage::extend('local', fn ($app, $config) => (new MoveDirectoryFilesystemTest)->failOnCopy());
    $disk = Storage::disk('local');

    $storedAsset = new Laraveltoolkit\StoredAssets\StoredAssets;

    $classReflection = new ReflectionClass($storedAsset);
    $method = $classReflection->getMethod('moveDirectory');
    $method->setAccessible(true);
    expect($method->invoke($storedAsset, $disk, 'foo', '/abc'))
        ->toBeFalse();
});

it('test move folder and fail on delete', function () {
    Storage::extend('local', fn ($app, $config) => (new MoveDirectoryFilesystemTest)->failOnDeleteDirectory());
    $disk = Storage::disk('local');

    $storedAsset = new Laraveltoolkit\StoredAssets\StoredAssets;

    $classReflection = new ReflectionClass($storedAsset);
    $method = $classReflection->getMethod('moveDirectory');
    $method->setAccessible(true);
    expect($method->invoke($storedAsset, $disk, 'foo', '/abc'))
        ->toBeFalse();
});
