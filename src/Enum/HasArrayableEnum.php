<?php

namespace Laraveltoolkit\Enum;

use Exception;
use Illuminate\Support\Collection;

trait HasArrayableEnum
{
    public static function toEnumArray(): array
    {
        throw_if(!enum_exists(self::class), Exception::class, self::class.' is not a valid enum');

        return collect(self::cases())
            ->mapWithKeys(fn($value, $key) => [$value->value => $value->label()])
            ->toArray();
    }

    public static function toValueLabel(string $labelKey = 'label', string $valueKey = 'value'): Collection
    {
        return collect(self::toEnumArray())->toValueLabelFromArray($labelKey, $valueKey);
    }

    public function label(): string
    {
        return $this->value;
    }
}
