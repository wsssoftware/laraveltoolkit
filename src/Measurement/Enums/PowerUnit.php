<?php

namespace Laraveltoolkit\Measurement\Enums;

use BcMath\Number;
use InvalidArgumentException;
use Laraveltoolkit\Measurement\Concerns\ConvertsLinearUnit;
use Laraveltoolkit\Measurement\Contracts\Unit;

enum PowerUnit: string implements Unit
{
    use ConvertsLinearUnit;

    case MICROWATT = 'microwatt';
    case MILLIWATT = 'milliwatt';
    case WATT = 'watt';
    case KILOWATT = 'kilowatt';
    case MEGAWATT = 'megawatt';
    case GIGAWATT = 'gigawatt';
    case MECHANICAL_HORSEPOWER = 'mechanical_horsepower';
    case METRIC_HORSEPOWER = 'metric_horsepower';
    case BTU_PER_HOUR = 'btu_per_hour';

    public function dimension(): string
    {
        return 'power';
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
            self::MICROWATT => 1,
            self::MILLIWATT => 1_000,
            self::WATT => 1_000_000,
            self::KILOWATT => 1_000_000_000,
            self::MEGAWATT => 1_000_000_000_000,
            self::GIGAWATT => 1_000_000_000_000_000,
            self::MECHANICAL_HORSEPOWER => 745_699_872,
            self::METRIC_HORSEPOWER => 735_498_750,
            self::BTU_PER_HOUR => 293_071,
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

        throw new InvalidArgumentException("Unknown power unit [{$unit}].");
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
        return "laraveltoolkit::measurement.power.units.{$this->value}.{$attribute}";
    }
}
