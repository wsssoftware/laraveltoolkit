<?php

namespace Laraveltoolkit\Measurement\Enums;

use BcMath\Number;
use InvalidArgumentException;
use Laraveltoolkit\Measurement\Contracts\Unit;

enum TemperatureUnit: string implements Unit
{
    case CELSIUS = 'celsius';
    case FAHRENHEIT = 'fahrenheit';
    case KELVIN = 'kelvin';
    case RANKINE = 'rankine';

    public function dimension(): string
    {
        return 'temperature';
    }

    public function formattedName(int|float $value, ?string $locale = null): string
    {
        return trans_choice(
            $this->translationKey('format'),
            abs($value),
            locale: $locale,
        );
    }

    public function fromReference(Number $referenceValue, int $scale): Number
    {
        return $referenceValue->sub($this->offset())->div($this->reference(), $scale);
    }

    public function name(?string $locale = null): string
    {
        return trans($this->translationKey('name'), locale: $locale);
    }

    public function reference(): Number
    {
        return new Number(match ($this) {
            self::CELSIUS, self::KELVIN => 9_000_000_000,
            self::FAHRENHEIT, self::RANKINE => 5_000_000_000,
        });
    }

    public function term(?string $locale = null): string
    {
        return trans($this->translationKey('term'), locale: $locale);
    }

    public function toReference(Number $value): Number
    {
        return $value->mul($this->reference())->add($this->offset());
    }

    public function toReferenceDelta(Number $value): Number
    {
        return $value->mul($this->reference());
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

        throw new InvalidArgumentException("Unknown temperature unit [{$unit}].");
    }

    private function aliases(): array
    {
        $aliases = [mb_strtolower($this->name), $this->value, ...$this->symbolAliases()];

        foreach (['en', 'pt_BR'] as $locale) {
            $aliases[] = mb_strtolower(trans($this->translationKey('name'), locale: $locale));
            $aliases[] = mb_strtolower(trans($this->translationKey('term'), locale: $locale));
        }

        return array_unique($aliases);
    }

    private function symbolAliases(): array
    {
        return match ($this) {
            self::CELSIUS => ['c', 'degc'],
            self::FAHRENHEIT => ['f', 'degf'],
            self::KELVIN => ['k'],
            self::RANKINE => ['r', 'degr'],
        };
    }

    private function offset(): int
    {
        return match ($this) {
            self::KELVIN, self::RANKINE => 0,
            self::CELSIUS => 2_458_350_000_000,
            self::FAHRENHEIT => 2_298_350_000_000,
        };
    }

    private function translationKey(string $attribute): string
    {
        return "laraveltoolkit::measurement.temperature.units.{$this->value}.{$attribute}";
    }
}
