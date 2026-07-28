<?php

namespace Laraveltoolkit\Measurement\Enums;

use BcMath\Number;
use InvalidArgumentException;
use Laraveltoolkit\Measurement\Concerns\ConvertsLinearUnit;
use Laraveltoolkit\Measurement\Contracts\Unit;

enum WeightUnit: string implements Unit
{
    use ConvertsLinearUnit;

    case NANOGRAM = 'nanogram';
    case MICROGRAM = 'microgram';
    case MILLIGRAM = 'milligram';
    case GRAM = 'gram';
    case KILOGRAM = 'kilogram';
    case METRIC_TON = 'metric_ton';
    case OUNCE = 'ounce';
    case POUND = 'pound';
    case STONE = 'stone';

    public function dimension(): string
    {
        return 'weight';
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
            self::NANOGRAM => 1,
            self::MICROGRAM => 1_000,
            self::MILLIGRAM => 1_000_000,
            self::GRAM => 1_000_000_000,
            self::KILOGRAM => 1_000_000_000_000,
            self::METRIC_TON => 1_000_000_000_000_000,
            self::OUNCE => 28_349_523_125,
            self::POUND => 453_592_370_000,
            self::STONE => 6_350_293_180_000,
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

        throw new InvalidArgumentException("Unknown weight unit [{$unit}].");
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
        return "laraveltoolkit::measurement.weight.units.{$this->value}.{$attribute}";
    }
}
