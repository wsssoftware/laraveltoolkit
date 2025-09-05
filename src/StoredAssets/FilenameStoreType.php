<?php

namespace Laraveltoolkit\StoredAssets;

use Ramsey\Uuid\Uuid;

enum FilenameStoreType: string
{
    case UUID = 'uuid';
    case KEY = 'key';

    public function getFilename(AssetIntent $intent, ?string $extension = null): string
    {
        return match ($this) {
            self::KEY => str($intent->getKey())->camel()->kebab()->toString(),
            self::UUID => Uuid::uuid7()->toString(),
        }.(! empty($extension) ? '.'.$extension : '');
    }
}
