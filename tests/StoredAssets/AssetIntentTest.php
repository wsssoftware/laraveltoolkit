<?php

use Illuminate\Http\UploadedFile;
use Laraveltoolkit\StoredAssets\Asset;
use Laraveltoolkit\StoredAssets\AssetIntent;
use Laraveltoolkit\StoredAssets\FilenameStoreType;

it('test create and store from pathname', function () {
    Storage::fake('local');
    $image = UploadedFile::fake()->image('image');
    $intent = AssetIntent::create($image->getPathname());
    $uuid = Str::uuid()->toString();

    expect($intent)
        ->toBeInstanceOf(AssetIntent::class)
        ->and($asset = $intent->store($uuid))
        ->toBeInstanceOf(Asset::class)
        ->and($asset->assetsUuid)
        ->toEqual($uuid)
        ->and($asset->size)
        ->toEqual($image->getSize())
        ->and($asset->extension)
        ->toEqual(File::guessExtension($image->getPathname()))
        ->and($asset->mimeType)
        ->toEqual(File::mimeType($image->getPathname()));
});

it('test create and store from content', function () {
    Storage::fake('local');
    $image = UploadedFile::fake()->image('image');
    $intent = AssetIntent::createFromContent(file_get_contents($image->getPathname()));
    $uuid = Str::uuid()->toString();

    expect($intent)
        ->toBeInstanceOf(AssetIntent::class)
        ->and($asset = $intent->store($uuid))
        ->toBeInstanceOf(Asset::class)
        ->and($asset->assetsUuid)
        ->toEqual($uuid)
        ->and($asset->size)
        ->toEqual($image->getSize())
        ->and($asset->extension)
        ->toEqual(File::guessExtension($image->getPathname()))
        ->and($asset->mimeType)
        ->toEqual(File::mimeType($image->getPathname()));
});

it('test create and store from resource', function () {
    Storage::fake('local');
    $image = UploadedFile::fake()->image('image');
    $intent = AssetIntent::createFromResource(fopen($image->getPathname(), 'r'));
    $uuid = Str::uuid()->toString();

    expect($intent)
        ->toBeInstanceOf(AssetIntent::class)
        ->and($asset = $intent->store($uuid))
        ->toBeInstanceOf(Asset::class)
        ->and($asset->assetsUuid)
        ->toEqual($uuid)
        ->and($asset->size)
        ->toEqual($image->getSize())
        ->and($asset->extension)
        ->toEqual(File::guessExtension($image->getPathname()))
        ->and($asset->mimeType)
        ->toEqual(File::mimeType($image->getPathname()));
});

it('test create and store from uploadedFile', function () {
    Storage::fake('local');
    $image = UploadedFile::fake()->image('image');
    $intent = AssetIntent::createFromUploadedFile($image);
    $uuid = Str::uuid()->toString();

    expect($intent)
        ->toBeInstanceOf(AssetIntent::class)
        ->and($asset = $intent->store($uuid))
        ->toBeInstanceOf(Asset::class)
        ->and($asset->assetsUuid)
        ->toEqual($uuid)
        ->and($asset->size)
        ->toEqual($image->getSize())
        ->and($asset->extension)
        ->toEqual(File::guessExtension($image->getPathname()))
        ->and($asset->mimeType)
        ->toEqual(File::mimeType($image->getPathname()));
});

it('test intent options', function () {
    Storage::fake('public');
    $image = UploadedFile::fake()->image('image');
    $intent = AssetIntent::create($image->getPathname());
    $class = new ReflectionClass($intent);

    expect($intent)
        ->toBeInstanceOf(AssetIntent::class)
        ->and($intent->asPublic())
        ->toBeInstanceOf(AssetIntent::class)
        ->and($class->getProperty('public')->getValue($intent))
        ->toBeTrue()
        ->and($intent->asPrivate())
        ->toBeInstanceOf(AssetIntent::class)
        ->and($class->getProperty('public')->getValue($intent))
        ->toBeFalse()
        ->and($intent->withKey('foo_bar'))
        ->toBeInstanceOf(AssetIntent::class)
        ->and($intent->getKey())
        ->toEqual('foo_bar')
        ->and($intent->withDisk('public'))
        ->toBeInstanceOf(AssetIntent::class)
        ->and($class->getProperty('disk')->getValue($intent))
        ->toBe('public')
        ->and($intent->withFilenameStoreType(FilenameStoreType::KEY))
        ->toBeInstanceOf(AssetIntent::class)
        ->and($class->getProperty('filenameStoreType')->getValue($intent))
        ->toBe(FilenameStoreType::KEY)
        ->and($intent->withOptions(['foo' => 'bar']))
        ->toBeInstanceOf(AssetIntent::class)
        ->and($class->getProperty('options')->getValue($intent))
        ->toBe(['foo' => 'bar'])
        ->and(fn() => $intent->withKey('$inv@lid'))
        ->toThrow('"$inv@lid" is not a valid asset key.');
});

it('test get content resource', function () {
    Storage::fake('public');
    $image = UploadedFile::fake()->image('image');
    $intent = AssetIntent::create($image->getPathname());
    $class = new ReflectionClass(AssetIntent::class);
    $intent2 = $class->newInstanceWithoutConstructor();
    $class->getProperty('pathname')->setValue($intent2, '/invalid/path/name');

    expect($intent)
        ->toBeInstanceOf(AssetIntent::class)
        ->and($class->getMethod('getContentStream')->invoke($intent))
        ->toBeResource()
        ->and($intent2)
        ->toBeInstanceOf(AssetIntent::class)
        ->and(fn() => $class->getMethod('getContentStream')->invoke($intent2))
        ->toThrow('Unable to read file from location: /invalid/path/name.');
});

it('test is publicity will put right options', function () {
    Storage::fake('local');
    $image = UploadedFile::fake()->image('image');
    $intent = AssetIntent::create($image->getPathname());
    $class = new ReflectionClass(AssetIntent::class);

    expect($intent)
        ->toBeInstanceOf(AssetIntent::class)
        ->and($intent->asPublic())
        ->toBeInstanceOf(AssetIntent::class)
        ->and($intent->store(Str::uuid()))
        ->toBeInstanceOf(Asset::class)
        ->and($options = $class->getProperty('options')->getValue($intent))
        ->toBeArray()
        ->toHaveKey('visibility')
        ->and($options['visibility'])
        ->toBe('public');
});
