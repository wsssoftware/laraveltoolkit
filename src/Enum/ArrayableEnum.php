<?php

namespace Laraveltoolkit\Enum;

use Illuminate\Support\Collection;

interface ArrayableEnum
{
    public static function toEnumArray(
        ?int $sortFlags = null,
        int $direction = SORT_ASC,
        ?array $only = null,
        ?array $except = null
    ): array;

    public static function toValueLabel(
        string $labelKey = 'label',
        string $valueKey = 'value',
        ?int $sortFlags = null,
        int $direction = SORT_ASC,
        ?array $only = null,
        ?array $except = null
    ): Collection;

    public static function toEnumArrayable(
        string $sortKey = 'label',
        ?int $sortFlags = SORT_REGULAR,
        int $direction = SORT_ASC,
        ?array $only = null,
        ?array $except = null
    ): array;

    public function label(): string;
}
