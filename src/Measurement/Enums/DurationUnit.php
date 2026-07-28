<?php

namespace Laraveltoolkit\Measurement\Enums;

use BcMath\Number;
use InvalidArgumentException;
use Laraveltoolkit\Measurement\Concerns\ConvertsLinearUnit;
use Laraveltoolkit\Measurement\Contracts\Unit;

enum DurationUnit: string implements Unit
{
    use ConvertsLinearUnit;

    case NANOSECOND = 'nanosecond';
    case MICROSECOND = 'microsecond';
    case MILLISECOND = 'millisecond';
    case SECOND = 'second';
    case MINUTE = 'minute';
    case HOUR = 'hour';
    case DAY = 'day';
    case WEEK = 'week';
    case MONTH = 'month';
    case YEAR = 'year';

    public function dimension(): string
    {
        return 'duration';
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
            self::NANOSECOND => 1,
            self::MICROSECOND => 1_000,
            self::MILLISECOND => 1_000_000,
            self::SECOND => 1_000_000_000,
            self::MINUTE => 60_000_000_000,
            self::HOUR => 3_600_000_000_000,
            self::DAY => 86_400_000_000_000,
            self::WEEK => 604_800_000_000_000,
            self::MONTH => 2_629_800_000_000_000,
            self::YEAR => 31_557_600_000_000_000,
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

        throw new InvalidArgumentException("Unknown duration unit [{$unit}].");
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
        return "laraveltoolkit::measurement.duration.units.{$this->value}.{$attribute}";
    }
}
