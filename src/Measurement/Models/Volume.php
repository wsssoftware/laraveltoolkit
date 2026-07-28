<?php

namespace Laraveltoolkit\Measurement\Models;

use InvalidArgumentException;
use Laraveltoolkit\Measurement\Contracts\Unit;
use Laraveltoolkit\Measurement\Enums\VolumeUnit;
use RoundingMode;

final class Volume extends Number
{
    public function __construct(
        int|float $value,
        public readonly VolumeUnit $unit = VolumeUnit::LITER,
        RoundingMode $roundingMode = RoundingMode::HalfAwayFromZero,
    ) {
        parent::__construct($value, $unit, $roundingMode);
    }

    public function unit(): VolumeUnit
    {
        return $this->unit;
    }

    public static function resolveUnit(string $unit): VolumeUnit
    {
        return VolumeUnit::resolve($unit);
    }

    public function to(VolumeUnit $unit): self
    {
        return $this->convert($unit);
    }

    protected static function newWithUnit(int|float $value, Unit $unit): static
    {
        if (! $unit instanceof VolumeUnit) {
            throw new InvalidArgumentException('A volume value requires a volume unit.');
        }

        return new self($value, $unit);
    }
}
