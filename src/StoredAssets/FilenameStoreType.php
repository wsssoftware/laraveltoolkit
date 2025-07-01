<?php

namespace Laraveltoolkit\StoredAssets;

use Illuminate\Support\Str;

enum FilenameStoreType: string
{
    case UUID = 'uuid';
    case KEY = 'key';

    public function getFilename(AssetIntent $intent, ?string $extension = null): string
    {
        return match ($this) {
                self::KEY => str($intent->getKey())->camel()->kebab()->toString(),
                self::UUID => Str::uuid()->toString(),
            }.(!empty($extension) ? '.'.$extension : '');
    }
}
