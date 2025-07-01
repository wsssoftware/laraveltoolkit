<?php

namespace Laraveltoolkit\Tests\Model;

use Illuminate\Database\Eloquent\Model;
use Laraveltoolkit\StoredAssets\HasStoredAssets;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string|\Laraveltoolkit\StoredAssets\Assets $image
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Product extends Model
{
    use HasStoredAssets;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'user_id',
        'image',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'int',
            'image' => ProductImageRecipe::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
