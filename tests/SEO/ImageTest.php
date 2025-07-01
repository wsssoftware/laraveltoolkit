<?php

use Illuminate\Filesystem\FilesystemAdapter;
use Laraveltoolkit\SEO\Image;

it('create and return an image', function () {
    /** @var FilesystemAdapter $disk */
    $disk = Storage::fake('public');
    $disk->put('foo_bar.png', 'example');
    $timestamp = $disk->lastModified('foo_bar.png');
    $image = new Image('public', 'foo_bar.png', 'alt');
    $image2 = new Image('public', 'not_found.png', 'alt');
    expect(Cache::offsetExists("seo_image:$image->disk:$image->path"))
        ->toBeFalse()
        ->and($image->toUrl())
        ->toBeString()
        ->toEndWith('?'.$timestamp)
        ->and(Cache::offsetExists("seo_image:$image->disk:$image->path"))
        ->toBeTrue()
        ->and($image2->toUrl())
        ->toBeString()
        ->toEndWith('not_found.png');
});
