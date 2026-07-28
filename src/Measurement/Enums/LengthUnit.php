<?php

namespace Laraveltoolkit\Measurement\Enums;

use BcMath\Number;
use InvalidArgumentException;
use Laraveltoolkit\Measurement\Concerns\ConvertsLinearUnit;
use Laraveltoolkit\Measurement\Contracts\Unit;

enum LengthUnit: string implements Unit
{
    use ConvertsLinearUnit;

    case NANOMETER = 'nanometer';
    case MICROMETER = 'micrometer';
    case MILLIMETER = 'millimeter';
    case CENTIMETER = 'centimeter';
    case DECIMETER = 'decimeter';
    case METER = 'meter';
    case DECAMETER = 'decameter';
    case HECTOMETER = 'hectometer';
    case KILOMETER = 'kilometer';
    case INCH = 'inch';
    case FOOT = 'foot';
    case YARD = 'yard';
    case MILE = 'mile';
    case NAUTICAL_MILE = 'nautical_mile';

    public function dimension(): string
    {
        return 'length';
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
            self::NANOMETER => 1,
            self::MICROMETER => 1_000,
            self::MILLIMETER => 1_000_000,
            self::CENTIMETER => 10_000_000,
            self::DECIMETER => 100_000_000,
            self::METER => 1_000_000_000,
            self::DECAMETER => 10_000_000_000,
            self::HECTOMETER => 100_000_000_000,
            self::KILOMETER => 1_000_000_000_000,
            self::INCH => 25_400_000,
            self::FOOT => 304_800_000,
            self::YARD => 914_400_000,
            self::MILE => 1_609_344_000_000,
            self::NAUTICAL_MILE => 1_852_000_000_000,
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

        throw new InvalidArgumentException("Unknown length unit [{$unit}].");
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
        return "laraveltoolkit::measurement.length.units.{$this->value}.{$attribute}";
    }
}
