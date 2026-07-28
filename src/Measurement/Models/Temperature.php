<?php

namespace Laraveltoolkit\Measurement\Models;

use InvalidArgumentException;
use Laraveltoolkit\Measurement\Contracts\Unit;
use Laraveltoolkit\Measurement\Enums\TemperatureUnit;
use RoundingMode;

final class Temperature extends Number
{
    public function __construct(
        int|float $value,
        public readonly TemperatureUnit $unit = TemperatureUnit::CELSIUS,
        RoundingMode $roundingMode = RoundingMode::HalfAwayFromZero,
    ) {
        parent::__construct($value, $unit, $roundingMode);
    }

    public function unit(): TemperatureUnit
    {
        return $this->unit;
    }

    public static function resolveUnit(string $unit): TemperatureUnit
    {
        return TemperatureUnit::resolve($unit);
    }

    public function add(
        Number|int|float $value,
        RoundingMode $roundingMode = RoundingMode::HalfAwayFromZero,
    ): static {
        $this->assertScalar($value);

        return parent::add($value, $roundingMode);
    }

    public function sub(
        Number|int|float $value,
        RoundingMode $roundingMode = RoundingMode::HalfAwayFromZero,
    ): static {
        $this->assertScalar($value);

        return parent::sub($value, $roundingMode);
    }

    public function mul(
        int|float $value,
        RoundingMode $roundingMode = RoundingMode::HalfAwayFromZero,
    ): static {
        return static::fromUnitValue(
            $this->decimalValue()->mul(static::scalarNumber($value)),
            $this->unit,
            $roundingMode,
        );
    }

    public function div(
        int|float $value,
        RoundingMode $roundingMode = RoundingMode::HalfAwayFromZero,
    ): static {
        return static::fromUnitValue(
            static::divideNumbers($this->decimalValue(), static::scalarNumber($value), $roundingMode),
            $this->unit,
            $roundingMode,
        );
    }

    public function difference(
        self $temperature,
        ?TemperatureUnit $unit = null,
    ): float {
        $unit ??= $this->unit;

        $difference = static::decimalValueOf($this, $unit)
            ->sub(static::decimalValueOf($temperature, $unit));

        return (float) (string) $difference;
    }

    public function to(TemperatureUnit $unit): self
    {
        return $this->convert($unit);
    }

    protected static function newWithUnit(int|float $value, Unit $unit): static
    {
        if (! $unit instanceof TemperatureUnit) {
            throw new InvalidArgumentException('A temperature value requires a temperature unit.');
        }

        return new self($value, $unit);
    }

    private function assertScalar(Number|int|float $value): void
    {
        if ($value instanceof Number) {
            throw new InvalidArgumentException(
                'Temperature arithmetic accepts scalar deltas; use difference() to compare temperatures.',
            );
        }
    }
}
