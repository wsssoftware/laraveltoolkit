<?php

namespace Laraveltoolkit\Enum;

use Exception;
use Illuminate\Support\Collection;

trait HasArrayableEnum
{
    public static function toEnumArray(
        ?int $sortFlags = null,
        int $direction = SORT_ASC,
        ?array $only = null,
        ?array $except = null
    ): array {
        throw_if(! enum_exists(self::class), Exception::class, self::class.' is not a valid enum');

        return collect(self::cases())
            ->mapWithKeys(fn ($value, $key) => [$value->value => $value->label()])
            ->when(! empty($only), fn (Collection $c) => $c->only($only))
            ->when(! empty($except), fn (Collection $c) => $c->except($except))
            ->when($sortFlags !== null, fn (Collection $c) => $c->collatorSort($sortFlags, $direction))
            ->toArray();
    }

    public static function toValueLabel(
        string $labelKey = 'label',
        string $valueKey = 'value',
        ?int $sortFlags = SORT_REGULAR,
        int $direction = SORT_ASC,
        ?array $only = null,
        ?array $except = null
    ): Collection {
        return collect(self::toEnumArray($sortFlags, $direction, $only, $except))
            ->toValueLabelFromArray($labelKey, $valueKey);
    }

    public function label(): string
    {
        return $this->value;
    }
}
