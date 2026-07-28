<?php

namespace Laraveltoolkit\Measurement\Models;

use InvalidArgumentException;
use Laraveltoolkit\Measurement\Contracts\Unit;
use Laraveltoolkit\Measurement\Enums\DurationUnit;
use Laraveltoolkit\Measurement\Enums\EnergyUnit;
use Laraveltoolkit\Measurement\Enums\PowerUnit;
use RoundingMode;

final class Power extends Number
{
    public function __construct(
        int|float $value,
        public readonly PowerUnit $unit = PowerUnit::WATT,
        RoundingMode $roundingMode = RoundingMode::HalfAwayFromZero,
    ) {
        parent::__construct($value, $unit, $roundingMode);
    }

    public function unit(): PowerUnit
    {
        return $this->unit;
    }

    public static function resolveUnit(string $unit): PowerUnit
    {
        return PowerUnit::resolve($unit);
    }

    public function energy(
        Duration $duration,
        EnergyUnit $unit = EnergyUnit::JOULE,
    ): Energy {
        $watts = self::decimalValueOf($this, PowerUnit::WATT);
        $seconds = self::decimalValueOf($duration, DurationUnit::SECOND);
        $joules = $watts->mul($seconds);

        return Energy::fromReferenceValue(
            EnergyUnit::JOULE->toReference($joules)->__toString(),
            EnergyUnit::JOULE,
        )->to($unit);
    }

    public function to(PowerUnit $unit): self
    {
        return $this->convert($unit);
    }

    protected static function newWithUnit(int|float $value, Unit $unit): static
    {
        if (! $unit instanceof PowerUnit) {
            throw new InvalidArgumentException('A power value requires a power unit.');
        }

        return new self($value, $unit);
    }
}
