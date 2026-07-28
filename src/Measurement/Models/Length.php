<?php

namespace Laraveltoolkit\Measurement\Models;

use InvalidArgumentException;
use Laraveltoolkit\Measurement\Contracts\Unit;
use Laraveltoolkit\Measurement\Enums\LengthUnit;
use RoundingMode;

final class Length extends Number
{
    public function __construct(
        int|float $value,
        public readonly LengthUnit $unit = LengthUnit::METER,
        RoundingMode $roundingMode = RoundingMode::HalfAwayFromZero,
    ) {
        parent::__construct($value, $unit, $roundingMode);
    }

    public function unit(): LengthUnit
    {
        return $this->unit;
    }

    public static function resolveUnit(string $unit): LengthUnit
    {
        return LengthUnit::resolve($unit);
    }

    public function to(LengthUnit $unit): self
    {
        return $this->convert($unit);
    }

    protected static function newWithUnit(int|float $value, Unit $unit): static
    {
        if (! $unit instanceof LengthUnit) {
            throw new InvalidArgumentException('A length value requires a length unit.');
        }

        return new self($value, $unit);
    }
}
