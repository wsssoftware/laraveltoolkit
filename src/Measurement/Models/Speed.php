<?php

namespace Laraveltoolkit\Measurement\Models;

use InvalidArgumentException;
use Laraveltoolkit\Measurement\Contracts\Unit;
use Laraveltoolkit\Measurement\Enums\SpeedUnit;
use RoundingMode;

final class Speed extends Number
{
    public function __construct(
        int|float $value,
        public readonly SpeedUnit $unit = SpeedUnit::KILOMETER_PER_HOUR,
        RoundingMode $roundingMode = RoundingMode::HalfAwayFromZero,
    ) {
        parent::__construct($value, $unit, $roundingMode);
    }

    public function unit(): SpeedUnit
    {
        return $this->unit;
    }

    public static function resolveUnit(string $unit): SpeedUnit
    {
        return SpeedUnit::resolve($unit);
    }

    public function to(SpeedUnit $unit): self
    {
        return $this->convert($unit);
    }

    protected static function newWithUnit(int|float $value, Unit $unit): static
    {
        if (! $unit instanceof SpeedUnit) {
            throw new InvalidArgumentException('A speed value requires a speed unit.');
        }

        return new self($value, $unit);
    }
}
