<?php

namespace Laraveltoolkit\Macros;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;

class BlueprintMacro
{
    public function __invoke(): void
    {
        $this->storedAsset();
    }

    public function storedAsset(): void
    {
        Blueprint::macro('storedAsset', function (string $column): ColumnDefinition {
            return $this->uuid($column)->index();
        });
    }
}
