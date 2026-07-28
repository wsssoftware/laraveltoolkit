<?php

namespace Laraveltoolkit\Measurement\Enums;

use BcMath\Number;
use InvalidArgumentException;
use Laraveltoolkit\Measurement\Concerns\ConvertsLinearUnit;
use Laraveltoolkit\Measurement\Contracts\Unit;

enum AreaUnit: string implements Unit
{
    use ConvertsLinearUnit;

    case SQUARE_MILLIMETER = 'square_millimeter';
    case SQUARE_CENTIMETER = 'square_centimeter';
    case SQUARE_DECIMETER = 'square_decimeter';
    case SQUARE_METER = 'square_meter';
    case ARE = 'are';
    case HECTARE = 'hectare';
    case SQUARE_KILOMETER = 'square_kilometer';
    case SQUARE_INCH = 'square_inch';
    case SQUARE_FOOT = 'square_foot';
    case SQUARE_YARD = 'square_yard';
    case ACRE = 'acre';
    case SQUARE_MILE = 'square_mile';

    public function dimension(): string
    {
        return 'area';
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
            self::SQUARE_MILLIMETER => 100,
            self::SQUARE_CENTIMETER => 10_000,
            self::SQUARE_DECIMETER => 1_000_000,
            self::SQUARE_METER => 100_000_000,
            self::ARE => 10_000_000_000,
            self::HECTARE => 1_000_000_000_000,
            self::SQUARE_KILOMETER => 100_000_000_000_000,
            self::SQUARE_INCH => 64_516,
            self::SQUARE_FOOT => 9_290_304,
            self::SQUARE_YARD => 83_612_736,
            self::ACRE => 404_685_642_240,
            self::SQUARE_MILE => 258_998_811_033_600,
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

        throw new InvalidArgumentException("Unknown area unit [{$unit}].");
    }

    private function aliases(): array
    {
        $aliases = [mb_strtolower($this->name), $this->value, ...$this->asciiAliases()];

        foreach (['en', 'pt_BR'] as $locale) {
            $aliases[] = mb_strtolower(trans($this->translationKey('name'), locale: $locale));
            $aliases[] = mb_strtolower(trans($this->translationKey('term'), locale: $locale));
        }

        return array_unique($aliases);
    }

    private function asciiAliases(): array
    {
        return match ($this) {
            self::SQUARE_MILLIMETER => ['mm2', 'mm^2'],
            self::SQUARE_CENTIMETER => ['cm2', 'cm^2'],
            self::SQUARE_DECIMETER => ['dm2', 'dm^2'],
            self::SQUARE_METER => ['m2', 'm^2', 'sqm'],
            self::SQUARE_KILOMETER => ['km2', 'km^2', 'sqkm'],
            self::SQUARE_INCH => ['in2', 'in^2'],
            self::SQUARE_FOOT => ['ft2', 'ft^2'],
            self::SQUARE_YARD => ['yd2', 'yd^2'],
            self::SQUARE_MILE => ['mi2', 'mi^2'],
            default => [],
        };
    }

    private function translationKey(string $attribute): string
    {
        return "laraveltoolkit::measurement.area.units.{$this->value}.{$attribute}";
    }
}
