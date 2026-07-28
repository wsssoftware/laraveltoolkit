<?php

namespace Laraveltoolkit\Measurement\Models;

use InvalidArgumentException;
use Laraveltoolkit\Measurement\Contracts\Unit;
use Laraveltoolkit\Measurement\Enums\DurationUnit;
use RoundingMode;

final class Duration extends Number
{
    public function __construct(
        int|float $value,
        public readonly DurationUnit $unit = DurationUnit::SECOND,
        RoundingMode $roundingMode = RoundingMode::HalfAwayFromZero,
    ) {
        parent::__construct($value, $unit, $roundingMode);
    }

    public function unit(): DurationUnit
    {
        return $this->unit;
    }

    public static function resolveUnit(string $unit): DurationUnit
    {
        return DurationUnit::resolve($unit);
    }

    public function to(DurationUnit $unit): self
    {
        return $this->convert($unit);
    }

    protected static function newWithUnit(int|float $value, Unit $unit): static
    {
        if (! $unit instanceof DurationUnit) {
            throw new InvalidArgumentException('A duration value requires a duration unit.');
        }

        return new self($value, $unit);
    }
}
