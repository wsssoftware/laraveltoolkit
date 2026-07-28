<?php

namespace Laraveltoolkit\Measurement\Models;

use InvalidArgumentException;
use Laraveltoolkit\Measurement\Contracts\Unit;
use Laraveltoolkit\Measurement\Enums\AreaUnit;
use RoundingMode;

final class Area extends Number
{
    public function __construct(
        int|float $value,
        public readonly AreaUnit $unit = AreaUnit::SQUARE_METER,
        RoundingMode $roundingMode = RoundingMode::HalfAwayFromZero,
    ) {
        parent::__construct($value, $unit, $roundingMode);
    }

    public function unit(): AreaUnit
    {
        return $this->unit;
    }

    public static function resolveUnit(string $unit): AreaUnit
    {
        return AreaUnit::resolve($unit);
    }

    public function to(AreaUnit $unit): self
    {
        return $this->convert($unit);
    }

    protected static function newWithUnit(int|float $value, Unit $unit): static
    {
        if (! $unit instanceof AreaUnit) {
            throw new InvalidArgumentException('An area value requires an area unit.');
        }

        return new self($value, $unit);
    }
}
