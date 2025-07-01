<?php

namespace Laraveltoolkit\StoredAssets;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Support\Collection;
use Laraveltoolkit\StoredAssets\Casts\AssetsCast;

class Assets extends Collection implements Castable
{
    public readonly ?string $uuid;

    public function __construct(
        array $assets = [],
    ) {
        parent::__construct($assets);
        $this->setUuid($this->first());
    }

    public static function castUsing(array $arguments): string
    {
        return AssetsCast::class;
    }

    private function setUuid($value): void
    {
        if (! empty($this->uuid)) {
            return;
        } elseif ($value instanceof Asset) {
            $this->uuid = $value->assetsUuid;
        } elseif (is_array($value)) {
            $this->uuid = $value['assets_uuid'];
        }
    }

    public function put($key, $value): self
    {
        $this->setUuid($value);

        return parent::put($key, $value);
    }

    public function toDatabase(): array
    {
        return collect($this->items)->map(fn (Asset $asset) => $asset->toDatabase())->values()->toArray();
    }

    public function __get($key): ?Asset
    {
        return $this->offsetExists($key) ? $this->offsetGet($key) : null;
    }
}
