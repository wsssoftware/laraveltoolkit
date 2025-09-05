<?php

namespace Laraveltoolkit\StoredAssets;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string $id
 * @property string $id_suffix
 * @property string $model
 * @property string $field
 * @property \Laraveltoolkit\StoredAssets\Assets $assets
 * @property \Illuminate\Support\Carbon $created_at
 */
class StoredAssetModel extends Model
{
    use HasUuids;

    public const UPDATED_AT = null;

    protected $table = 'stored_assets';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'model',
        'field',
        'assets',
        'created_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'string',
            'id_suffix' => 'string',
            'model' => 'string',
            'field' => 'string',
            'assets' => Assets::class,
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the parent assetable model
     */
    public function assetable(): MorphTo
    {
        return $this->morphTo('assetable', 'model', 'id', $this->field);
    }
}
