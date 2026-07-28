<?php

namespace Laraveltoolkit\Measurement\Enums;

use BcMath\Number;
use InvalidArgumentException;
use Laraveltoolkit\Measurement\Concerns\ConvertsLinearUnit;
use Laraveltoolkit\Measurement\Contracts\Unit;

enum EnergyUnit: string implements Unit
{
    use ConvertsLinearUnit;

    case JOULE = 'joule';
    case KILOJOULE = 'kilojoule';
    case MEGAJOULE = 'megajoule';
    case GIGAJOULE = 'gigajoule';
    case WATT_HOUR = 'watt_hour';
    case KILOWATT_HOUR = 'kilowatt_hour';
    case MEGAWATT_HOUR = 'megawatt_hour';
    case CALORIE = 'calorie';
    case KILOCALORIE = 'kilocalorie';
    case BTU = 'btu';

    public function dimension(): string
    {
        return 'energy';
    }

    public function formattedName(int|float $value, ?string $locale = null): string
    {
        return trans_choice($this->translationKey('format'), abs($value), locale: $locale);
    }

    public function name(?string $locale = null): string
    {
        return trans($this->translationKey('name'), locale: $locale);
    }

    public function reference(): Number
    {
        return new Number(match ($this) {
            self::JOULE => 1_000_000,
            self::KILOJOULE => 1_000_000_000,
            self::MEGAJOULE => 1_000_000_000_000,
            self::GIGAJOULE => 1_000_000_000_000_000,
            self::WATT_HOUR => 3_600_000_000,
            self::KILOWATT_HOUR => 3_600_000_000_000,
            self::MEGAWATT_HOUR => 3_600_000_000_000_000,
            self::CALORIE => 4_184_000,
            self::KILOCALORIE => 4_184_000_000,
            self::BTU => 1_055_055_853,
        });
    }

    public function term(?string $locale = null): string
    {
        return trans($this->translationKey('term'), locale: $locale);
    }

    public function value(): string
    {
        return $this->value;
    }

    public static function resolve(string $unit): self
    {
        $unit = mb_strtolower(trim($unit));

        foreach (self::cases() as $case) {
            if (in_array($unit, $case->aliases(), true)) {
                return $case;
            }
        }

        throw new InvalidArgumentException("Unknown energy unit [{$unit}].");
    }

    private function aliases(): array
    {
        $aliases = [mb_strtolower($this->name), $this->value];

        foreach (['en', 'pt_BR'] as $locale) {
            $aliases[] = mb_strtolower(trans($this->translationKey('name'), locale: $locale));
            $aliases[] = mb_strtolower(trans($this->translationKey('term'), locale: $locale));
        }

        return array_unique($aliases);
    }

    private function translationKey(string $attribute): string
    {
        return "laraveltoolkit::measurement.energy.units.{$this->value}.{$attribute}";
    }
}
