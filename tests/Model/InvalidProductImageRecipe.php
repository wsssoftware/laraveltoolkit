<?php

namespace Laraveltoolkit\Tests\Model;

use Illuminate\Support\Collection;
use Laraveltoolkit\StoredAssets\AssetIntent;
use Laraveltoolkit\StoredAssets\Recipe;

class InvalidProductImageRecipe extends Recipe
{
    /**
     * {@inheritDoc}
     */
    protected function prepareForSave(AssetIntent $baseAsset): AssetIntent|Collection
    {
        return collect([
            $baseAsset->withKey('default'),
            AssetIntent::create($baseAsset->pathname)->withKey('default'),
        ]);
    }
}
