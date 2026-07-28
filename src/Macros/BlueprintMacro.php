<?php

namespace Laraveltoolkit\Macros;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;

class BlueprintMacro
{
    public function __invoke(): void
    {
        $this->measurement();
        $this->storedAsset();
    }

    public function measurement(): void
    {
        Blueprint::macro('measurement', function (string $column): ColumnDefinition {
            return $this->json($column);
        });
    }

    public function storedAsset(): void
    {
        Blueprint::macro('storedAsset', function (string $column): ColumnDefinition {
            return $this->uuid($column)->index();
        });
    }
}
