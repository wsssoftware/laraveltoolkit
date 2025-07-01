<?php

namespace Laraveltoolkit\StoredAssets\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Laraveltoolkit\StoredAssets\HasStoredAssets;
use Laraveltoolkit\StoredAssets\Recipe;

class StoredAssetCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        $implementsTrait = in_array(HasStoredAssets::class, class_uses_recursive($model::class));
        if (! empty($value) && ! $value instanceof Recipe && $model->isRelation($key) && $implementsTrait) {
            return $model->getRelationValue($key)->assets;
        }

        return $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value;
    }
}
