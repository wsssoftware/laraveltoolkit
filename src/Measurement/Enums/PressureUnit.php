<?php

namespace Laraveltoolkit\Measurement\Enums;

use BcMath\Number;
use InvalidArgumentException;
use Laraveltoolkit\Measurement\Concerns\ConvertsLinearUnit;
use Laraveltoolkit\Measurement\Contracts\Unit;

enum PressureUnit: string implements Unit
{
    use ConvertsLinearUnit;

    case PASCAL = 'pascal';
    case HECTOPASCAL = 'hectopascal';
    case KILOPASCAL = 'kilopascal';
    case MEGAPASCAL = 'megapascal';
    case MILLIBAR = 'millibar';
    case BAR = 'bar';
    case ATMOSPHERE = 'atmosphere';
    case PSI = 'psi';
    case TORR = 'torr';
    case MILLIMETER_OF_MERCURY = 'millimeter_of_mercury';
    case INCH_OF_MERCURY = 'inch_of_mercury';

    public function dimension(): string
    {
        return 'pressure';
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
            self::PASCAL => 1_000_000_000,
            self::HECTOPASCAL, self::MILLIBAR => 100_000_000_000,
            self::KILOPASCAL => 1_000_000_000_000,
            self::MEGAPASCAL => 1_000_000_000_000_000,
            self::BAR => 100_000_000_000_000,
            self::ATMOSPHERE => 101_325_000_000_000,
            self::PSI => 6_894_757_293_168,
            self::TORR => 133_322_368_421,
            self::MILLIMETER_OF_MERCURY => 133_322_387_415,
            self::INCH_OF_MERCURY => 3_386_389_000_000,
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

        throw new InvalidArgumentException("Unknown pressure unit [{$unit}].");
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
        return "laraveltoolkit::measurement.pressure.units.{$this->value}.{$attribute}";
    }
}
