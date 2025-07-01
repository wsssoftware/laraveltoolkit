<?php

namespace Laraveltoolkit\StoredAssets\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Laraveltoolkit\StoredAssets\Asset;
use Laraveltoolkit\StoredAssets\Assets;

class AssetsCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return json_validate($value)
            ? new Assets(
                collect(json_decode($value, true))
                    ->mapWithKeys(fn (array $asset) => [$asset['k'] => Asset::fromDatabase($model->id, $asset)])
                    ->all(),
            )
            : $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        return $value instanceof Assets ? json_encode($value->toDatabase()) : $value;
    }
}
