<?php

namespace Laraveltoolkit\Measurement\Enums;

use BcMath\Number;
use InvalidArgumentException;
use Laraveltoolkit\Measurement\Concerns\ConvertsLinearUnit;
use Laraveltoolkit\Measurement\Contracts\Unit;

enum VolumeUnit: string implements Unit
{
    use ConvertsLinearUnit;

    case NANOLITER = 'nanoliter';
    case MICROLITER = 'microliter';
    case MILLILITER = 'milliliter';
    case CENTILITER = 'centiliter';
    case DECILITER = 'deciliter';
    case LITER = 'liter';
    case CUBIC_MILLIMETER = 'cubic_millimeter';
    case CUBIC_CENTIMETER = 'cubic_centimeter';
    case CUBIC_METER = 'cubic_meter';
    case CUBIC_INCH = 'cubic_inch';
    case CUBIC_FOOT = 'cubic_foot';
    case US_TEASPOON = 'us_teaspoon';
    case US_TABLESPOON = 'us_tablespoon';
    case US_FLUID_OUNCE = 'us_fluid_ounce';
    case US_CUP = 'us_cup';
    case US_PINT = 'us_pint';
    case US_QUART = 'us_quart';
    case US_GALLON = 'us_gallon';
    case IMPERIAL_PINT = 'imperial_pint';
    case IMPERIAL_GALLON = 'imperial_gallon';

    public function dimension(): string
    {
        return 'volume';
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
            self::NANOLITER => 1,
            self::MICROLITER, self::CUBIC_MILLIMETER => 1_000,
            self::MILLILITER, self::CUBIC_CENTIMETER => 1_000_000,
            self::CENTILITER => 10_000_000,
            self::DECILITER => 100_000_000,
            self::LITER => 1_000_000_000,
            self::CUBIC_METER => 1_000_000_000_000,
            self::CUBIC_INCH => 16_387_064,
            self::CUBIC_FOOT => 28_316_846_592,
            self::US_TEASPOON => '4928921.59375',
            self::US_TABLESPOON => '14786764.78125',
            self::US_FLUID_OUNCE => '29573529.5625',
            self::US_CUP => '236588236.5',
            self::US_PINT => 473_176_473,
            self::US_QUART => 946_352_946,
            self::US_GALLON => 3_785_411_784,
            self::IMPERIAL_PINT => 568_261_250,
            self::IMPERIAL_GALLON => 4_546_090_000,
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

        throw new InvalidArgumentException("Unknown volume unit [{$unit}].");
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
        return "laraveltoolkit::measurement.volume.units.{$this->value}.{$attribute}";
    }
}
