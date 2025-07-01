<?php

use Illuminate\Http\UploadedFile;
use Laraveltoolkit\Facades\StoredAssets;
use Laraveltoolkit\StoredAssets\Assets;
use Laraveltoolkit\StoredAssets\StoredAssetModel;
use Laraveltoolkit\Tests\Model\Product;
use Laraveltoolkit\Tests\Model\ProductImageRecipe;

it('can cast a file to recipe', function () {
    $product = new Product;

    $product->image = UploadedFile::fake()->image('image.jpg');

    expect($product->image)->toBeInstanceOf(ProductImageRecipe::class);
});

it('do nothing on null value', function () {
    $product = new Product;

    $product->image = null;

    expect($product->image)->toBeNull();
});

it('can save a file', function () {
    Storage::fake('local');
    $product = new Product(['id' => 1]);
    $product->image = __DIR__.'/../TestCase.php';
    expect($product->save())
        ->toBeTrue()
        ->and($product->image_uuid)
        ->toBeUuid()
        ->and($product->image)
        ->toBeInstanceOf(Assets::class);
});

it('can save on a null', function () {
    $product = new Product(['id' => 1]);
    $product->image = null;
    expect($product->save())
        ->toBeTrue()
        ->and($product->image_uuid)
        ->toBeNull()
        ->and($product->image)
        ->toBeNull();
});

it('can fail on save', function () {
    Storage::fake('local');
    $product = new Product(['id' => 1]);
    $product->image = __DIR__.'/../TestCase.php';

    StoredAssets::partialMock()->shouldReceive('newModel')
        ->once()
        ->andReturn(new class extends StoredAssetModel
        {
            public function save(array $options = []): bool
            {
                return false;
            }
        });

    expect($product->save())
        ->toBeFalse();
});
