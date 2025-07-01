<?php

use Illuminate\Contracts\Filesystem\Filesystem;
use Laraveltoolkit\StoredAssets\Asset;
use Laraveltoolkit\StoredAssets\Assets;

it('test assets methods', function () {
    $uuid = Str::uuid()->toString();
    $asset1 = new Asset($uuid, 'local', 'default', 'foo.zip', 'zip', 'app/zip', 12);
    $asset2 = new Asset($uuid, 'local', 'thumb', 'foo.zip', 'zip', 'app/zip', 12);
    $assets = new Assets([
        'default' => $asset1,
    ]);
    $assets2 = new Assets([
        'default' => ['assets_uuid' => $uuid],
    ]);

    expect($assets->uuid)
        ->toEqual($uuid)
        ->and($assets->default)
        ->toBeInstanceOf(Asset::class)
        ->and($assets2->uuid)
        ->toEqual($uuid)
        ->and($assets->put('thumb', $asset2))
        ->toBeInstanceOf(Assets::class)
        ->and($assets->thumb)
        ->toBeInstanceOf(Asset::class)
        ->and($assets->abc)
        ->toBeNull()
        ->and($assets->toDatabase())
        ->toHaveCount(2)
        ->each
        ->toHaveKeys(['d', 'k', 'p', 'e', 'm', 's']);

});

it('test asset methods', function () {
    $disk = Storage::fake('local');
    $disk->put('/foo_test.txt', 'abc');
    $asset = Asset::fromDatabase(Str::uuid(), [
        'd' => 'local',
        'k' => 'default',
        'p' => '/foo_test.txt',
        'e' => 'zip',
        'm' => 'app/zip',
        's' => 12,
    ]);

    expect($asset->assetsUuid)
        ->toBeUuid()
        ->and($asset->toArray())
        ->toBeArray()
        ->toHaveKeys(['assets_uuid', 'disk', 'key', 'pathname', 'extension', 'mime_type', 'size'])
        ->and($asset->toDatabase())
        ->toBeArray()
        ->toHaveKeys(['d', 'k', 'p', 'e', 'm', 's'])
        ->and($asset->disk())
        ->toBeInstanceOf(Filesystem::class)
        ->and($asset->get())
        ->toEqual('abc')
        ->and($asset->readStream())
        ->toBeResource()
        ->and($asset->url())
        ->toBeString();

});
