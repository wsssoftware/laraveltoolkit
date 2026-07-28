<?php

namespace Laraveltoolkit\Measurement\Models;

use InvalidArgumentException;
use Laraveltoolkit\Measurement\Contracts\Unit;
use Laraveltoolkit\Measurement\Enums\WeightUnit;
use RoundingMode;

final class Weight extends Number
{
    public function __construct(
        int|float $value,
        public readonly WeightUnit $unit = WeightUnit::KILOGRAM,
        RoundingMode $roundingMode = RoundingMode::HalfAwayFromZero,
    ) {
        parent::__construct($value, $unit, $roundingMode);
    }

    public function unit(): WeightUnit
    {
        return $this->unit;
    }

    public static function resolveUnit(string $unit): WeightUnit
    {
        return WeightUnit::resolve($unit);
    }

    public function to(WeightUnit $unit): self
    {
        return $this->convert($unit);
    }

    protected static function newWithUnit(int|float $value, Unit $unit): static
    {
        if (! $unit instanceof WeightUnit) {
            throw new InvalidArgumentException('A weight value requires a weight unit.');
        }

        return new self($value, $unit);
    }
}
