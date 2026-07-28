<?php

namespace Laraveltoolkit\Measurement\Models;

use InvalidArgumentException;
use Laraveltoolkit\Measurement\Contracts\Unit;
use Laraveltoolkit\Measurement\Enums\PressureUnit;
use RoundingMode;

final class Pressure extends Number
{
    public function __construct(
        int|float $value,
        public readonly PressureUnit $unit = PressureUnit::PASCAL,
        RoundingMode $roundingMode = RoundingMode::HalfAwayFromZero,
    ) {
        parent::__construct($value, $unit, $roundingMode);
    }

    public function unit(): PressureUnit
    {
        return $this->unit;
    }

    public static function resolveUnit(string $unit): PressureUnit
    {
        return PressureUnit::resolve($unit);
    }

    public function to(PressureUnit $unit): self
    {
        return $this->convert($unit);
    }

    protected static function newWithUnit(int|float $value, Unit $unit): static
    {
        if (! $unit instanceof PressureUnit) {
            throw new InvalidArgumentException('A pressure value requires a pressure unit.');
        }

        return new self($value, $unit);
    }
}
