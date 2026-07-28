<?php

namespace Laraveltoolkit\Measurement\Models;

use DivisionByZeroError;
use InvalidArgumentException;
use Laraveltoolkit\Measurement\Contracts\Unit;
use Laraveltoolkit\Measurement\Enums\DurationUnit;
use Laraveltoolkit\Measurement\Enums\EnergyUnit;
use Laraveltoolkit\Measurement\Enums\PowerUnit;
use RoundingMode;

final class Energy extends Number
{
    public function __construct(
        int|float $value,
        public readonly EnergyUnit $unit = EnergyUnit::JOULE,
        RoundingMode $roundingMode = RoundingMode::HalfAwayFromZero,
    ) {
        parent::__construct($value, $unit, $roundingMode);
    }

    public function unit(): EnergyUnit
    {
        return $this->unit;
    }

    public static function resolveUnit(string $unit): EnergyUnit
    {
        return EnergyUnit::resolve($unit);
    }

    public function power(
        Duration $duration,
        PowerUnit $unit = PowerUnit::WATT,
    ): Power {
        $seconds = self::decimalValueOf($duration, DurationUnit::SECOND);

        if ($seconds->compare(0) === 0) {
            throw new DivisionByZeroError('Cannot calculate power from a zero duration.');
        }

        $watts = self::divideNumbers(
            self::decimalValueOf($this, EnergyUnit::JOULE),
            $seconds,
        );

        return Power::fromReferenceValue(
            PowerUnit::WATT->toReference($watts)->__toString(),
            PowerUnit::WATT,
        )->to($unit);
    }

    public function to(EnergyUnit $unit): self
    {
        return $this->convert($unit);
    }

    protected static function newWithUnit(int|float $value, Unit $unit): static
    {
        if (! $unit instanceof EnergyUnit) {
            throw new InvalidArgumentException('An energy value requires an energy unit.');
        }

        return new self($value, $unit);
    }
}
