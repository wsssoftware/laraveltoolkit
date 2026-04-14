<?php

use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Laraveltoolkit\Facades\StoredAssets;
use Laraveltoolkit\StoredAssets\Assets;
use Laraveltoolkit\StoredAssets\Recipe;
use Laraveltoolkit\StoredAssets\StoredAssetModel;
use Laraveltoolkit\Tests\Model\InvalidProductImageRecipe;
use Laraveltoolkit\Tests\Model\Product;
use Laraveltoolkit\Tests\Model\ProductImageRecipe;

it('test fail parse directly from Recipe class', function () {
    expect(fn () => Recipe::parse(new Product, 'image', ''))
        ->toThrow('You cannot call parse directly from Recipe class');
});

it('test parse method', function () {
    $model = new Product;
    $uploadedFile = UploadedFile::fake()->image('image.jpg');
    $invalidUuid = Str::uuid()->toString();
    $validUuid = Str::uuid()->toString();

    StoredAssetModel::create([
        'id' => $validUuid,
        'model' => Product::class,
        'field' => 'image',
        'assets' => new Assets,
    ]);

    expect($recipe = ProductImageRecipe::parse($model, 'image', $uploadedFile))
        ->toBeInstanceOf(Recipe::class)
        ->and(ProductImageRecipe::parse($model, 'image', $uploadedFile->getPathname()))
        ->toBeInstanceOf(Recipe::class)
        ->and(ProductImageRecipe::parse($model, 'image', new File($uploadedFile->getPathname())))
        ->toBeInstanceOf(Recipe::class)
        ->and(ProductImageRecipe::parse($model, 'image', $recipe))
        ->toEqual($recipe)
        ->and(ProductImageRecipe::parse($model, 'image', $validUuid))
        ->toBeUuid()
        ->and(fn () => ProductImageRecipe::parse($model, 'image', $invalidUuid))
        ->toThrow("On field \"image\" from model \"Laraveltoolkit\Tests\Model\Product\", the the provided value \"$invalidUuid\" does not appears to be a valid uuid or does not exists on \"stored_assets\" table.");
});

it('test fail on duplicated key', function () {
    $model = new Product;
    $uploadedFile = UploadedFile::fake()->image('image.jpg');

    expect($recipe = InvalidProductImageRecipe::parse($model, 'image', $uploadedFile))
        ->toBeInstanceOf(InvalidProductImageRecipe::class)
        ->and(fn () => $recipe->save())
        ->toThrow('You may not has two asset with same name key. 2 found on "default');
});

it('test save', function () {
    $disk = Storage::fake('local');
    $model = new Product;
    $uploadedFile = UploadedFile::fake()->image('image.jpg');

    expect($recipe = ProductImageRecipe::parse($model, 'image', $uploadedFile))
        ->toBeInstanceOf(ProductImageRecipe::class)
        ->and($uuid = $recipe->save())
        ->toBeUuid()
        ->and($disk->exists(StoredAssets::path($uuid)))
        ->toBeTrue();
});
