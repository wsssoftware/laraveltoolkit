<?php

namespace Laraveltoolkit\Measurement\Enums;

use BcMath\Number;
use InvalidArgumentException;
use Laraveltoolkit\Measurement\Concerns\ConvertsLinearUnit;
use Laraveltoolkit\Measurement\Contracts\Unit;

enum SpeedUnit: string implements Unit
{
    use ConvertsLinearUnit;

    case MILLIMETER_PER_SECOND = 'millimeter_per_second';
    case CENTIMETER_PER_SECOND = 'centimeter_per_second';
    case METER_PER_SECOND = 'meter_per_second';
    case METER_PER_MINUTE = 'meter_per_minute';
    case KILOMETER_PER_HOUR = 'kilometer_per_hour';
    case KILOMETER_PER_SECOND = 'kilometer_per_second';
    case FOOT_PER_SECOND = 'foot_per_second';
    case MILE_PER_HOUR = 'mile_per_hour';
    case KNOT = 'knot';

    public function dimension(): string
    {
        return 'speed';
    }

    public function formattedName(int|float $value, ?string $locale = null): string
    {
        return trans_choice(
            $this->translationKey('format'),
            abs($value),
            locale: $locale,
        );
    }

    public function name(?string $locale = null): string
    {
        return trans($this->translationKey('name'), locale: $locale);
    }

    public function reference(): Number
    {
        return new Number(match ($this) {
            self::MILLIMETER_PER_SECOND => 3_600_000,
            self::CENTIMETER_PER_SECOND => 36_000_000,
            self::METER_PER_SECOND => 3_600_000_000,
            self::METER_PER_MINUTE => 60_000_000,
            self::KILOMETER_PER_HOUR => 1_000_000_000,
            self::KILOMETER_PER_SECOND => 3_600_000_000_000,
            self::FOOT_PER_SECOND => 1_097_280_000,
            self::MILE_PER_HOUR => 1_609_344_000,
            self::KNOT => 1_852_000_000,
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

        throw new InvalidArgumentException("Unknown speed unit [{$unit}].");
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
        return "laraveltoolkit::measurement.speed.units.{$this->value}.{$attribute}";
    }
}
