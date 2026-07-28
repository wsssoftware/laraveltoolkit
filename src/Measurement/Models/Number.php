<?php

namespace Laraveltoolkit\Measurement\Models;

use BcMath\Number as BcNumber;
use DivisionByZeroError;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Number as LaravelNumber;
use InvalidArgumentException;
use JsonSerializable;
use Laraveltoolkit\Measurement\Casts\MeasurementCast;
use Laraveltoolkit\Measurement\Contracts\Unit;
use OverflowException;
use RoundingMode;

abstract class Number implements Arrayable, Castable, JsonSerializable
{
    protected const int INTERNAL_SCALE = 24;

    private BcNumber $referenceValue;

    public function __construct(
        int|float $value,
        Unit $unit,
        RoundingMode $roundingMode = RoundingMode::HalfAwayFromZero,
    ) {
        $this->referenceValue = self::roundReference(
            $unit->toReference(self::scalarToNumber($value)),
            $roundingMode,
        );
    }

    abstract public function unit(): Unit;

    abstract public static function resolveUnit(string $unit): Unit;

    abstract protected static function newWithUnit(int|float $value, Unit $unit): static;

    public static function castUsing(array $arguments): CastsAttributes
    {
        return new MeasurementCast(static::class);
    }

    public static function fromReferenceValue(int|float|string $referenceValue, Unit $unit): static
    {
        $number = static::newWithUnit(0, $unit);
        $number->referenceValue = self::scalarToNumber($referenceValue);

        return $number;
    }

    public function value(): float
    {
        return (float) (string) $this->decimalValue();
    }

    public function referenceValue(): int|float|string
    {
        $value = $this->referenceValueString();

        if (! str_contains($value, '.')) {
            if ($this->referenceValue->compare(PHP_INT_MIN) >= 0
                && $this->referenceValue->compare(PHP_INT_MAX) <= 0) {
                return (int) $value;
            }

            return $value;
        }

        $float = (float) $value;

        return is_finite($float) ? $float : $value;
    }

    public function referenceValueString(): string
    {
        return self::normalizeDecimalString((string) $this->referenceValue);
    }

    public function format(
        ?int $precision = null,
        ?int $maxPrecision = null,
        bool $short = true,
        ?string $locale = null,
    ): string|false {
        $locale ??= app()->getLocale();
        $value = LaravelNumber::format(
            $this->value(),
            $precision,
            $maxPrecision,
            $locale,
        );

        if ($value === false) {
            return false;
        }

        if ($short) {
            return "{$value} {$this->unit()->term($locale)}";
        }

        $displayValue = LaravelNumber::parse($value, locale: $locale);
        $displayValue = $displayValue === false ? $this->value() : $displayValue;
        $postfix = $this->unit()->formattedName($displayValue, $locale);

        return "{$value} {$postfix}";
    }

    public function add(
        Number|int|float $value,
        RoundingMode $roundingMode = RoundingMode::HalfAwayFromZero,
    ): static {
        return $this->newInstance(
            self::roundReference(
                $this->referenceValue->add($this->operandReferenceValue($value)),
                $roundingMode,
            ),
            $this->unit(),
        );
    }

    public function sub(
        Number|int|float $value,
        RoundingMode $roundingMode = RoundingMode::HalfAwayFromZero,
    ): static {
        return $this->newInstance(
            self::roundReference(
                $this->referenceValue->sub($this->operandReferenceValue($value)),
                $roundingMode,
            ),
            $this->unit(),
        );
    }

    public function mul(
        int|float $value,
        RoundingMode $roundingMode = RoundingMode::HalfAwayFromZero,
    ): static {
        return $this->newInstance(
            self::roundReference(
                $this->referenceValue->mul(self::scalarToNumber($value)),
                $roundingMode,
            ),
            $this->unit(),
        );
    }

    public function div(
        int|float $value,
        RoundingMode $roundingMode = RoundingMode::HalfAwayFromZero,
    ): static {
        $divisor = self::scalarToNumber($value);

        if ($divisor->compare(0) === 0) {
            throw new DivisionByZeroError('Division by zero');
        }

        return $this->newInstance(
            self::divideNumbers($this->referenceValue, $divisor, $roundingMode),
            $this->unit(),
        );
    }

    public function convert(Unit $unit): static
    {
        $this->assertCompatible($unit);

        if ($unit === $this->unit()) {
            return $this;
        }

        return $this->newInstance($this->referenceValue, $unit);
    }

    public function compare(Number $number): int
    {
        $this->assertCompatible($number->unit());

        return $this->referenceValue->compare($number->referenceValue);
    }

    public function equals(Number $number): bool
    {
        return $this->compare($number) === 0;
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value(),
            'unit' => $this->unit()->value(),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    protected function assertCompatible(Unit $unit): void
    {
        if ($this->unit()->dimension() !== $unit->dimension()) {
            throw new InvalidArgumentException(sprintf(
                'Cannot operate on [%s] and [%s] measurements.',
                $this->unit()->dimension(),
                $unit->dimension(),
            ));
        }
    }

    protected function decimalValue(?Unit $unit = null): BcNumber
    {
        $unit ??= $this->unit();
        $this->assertCompatible($unit);

        return $unit->fromReference($this->referenceValue, self::INTERNAL_SCALE);
    }

    protected static function decimalValueOf(Number $number, Unit $unit): BcNumber
    {
        $number->assertCompatible($unit);

        return $unit->fromReference($number->referenceValue, self::INTERNAL_SCALE);
    }

    protected static function divideNumbers(
        BcNumber $dividend,
        BcNumber $divisor,
        RoundingMode $roundingMode = RoundingMode::HalfAwayFromZero,
    ): BcNumber {
        if ($divisor->compare(0) === 0) {
            throw new DivisionByZeroError('Division by zero');
        }

        return $dividend
            ->div($divisor, self::INTERNAL_SCALE + 1)
            ->round(self::INTERNAL_SCALE, $roundingMode);
    }

    protected static function fromUnitValue(
        BcNumber $value,
        Unit $unit,
        RoundingMode $roundingMode = RoundingMode::HalfAwayFromZero,
    ): static {
        return static::fromReferenceValue(
            self::roundReference($unit->toReference($value), $roundingMode)->__toString(),
            $unit,
        );
    }

    protected static function scalarNumber(int|float $value): BcNumber
    {
        return self::scalarToNumber($value);
    }

    private function newInstance(BcNumber $referenceValue, Unit $unit): static
    {
        $number = static::newWithUnit(0, $unit);
        $number->referenceValue = $referenceValue;

        return $number;
    }

    private function operandReferenceValue(Number|int|float $value): BcNumber
    {
        if (! $value instanceof Number) {
            return $this->unit()->toReferenceDelta(self::scalarToNumber($value));
        }

        $this->assertCompatible($value->unit());

        return $value->referenceValue;
    }

    private static function roundReference(BcNumber $value, RoundingMode $roundingMode): BcNumber
    {
        return $value->round(self::INTERNAL_SCALE, $roundingMode);
    }

    private static function scalarToNumber(int|float|string|BcNumber $value): BcNumber
    {
        if ($value instanceof BcNumber) {
            return $value;
        }

        if (is_float($value)) {
            if (! is_finite($value)) {
                throw new OverflowException('The measurement requires a finite numeric value.');
            }

            $value = self::floatToDecimalString($value);
        }

        return new BcNumber($value);
    }

    private static function floatToDecimalString(float $value): string
    {
        $encoded = json_encode($value, JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR);

        if (! str_contains(strtolower($encoded), 'e')) {
            return $encoded;
        }

        [$coefficient, $exponent] = preg_split('/e/i', $encoded);
        $negative = str_starts_with($coefficient, '-');
        $coefficient = ltrim($coefficient, '+-');
        [$integer, $fraction] = array_pad(explode('.', $coefficient, 2), 2, '');
        $digits = $integer.$fraction;
        $decimalPosition = strlen($integer) + (int) $exponent;

        if ($decimalPosition <= 0) {
            $decimal = '0.'.str_repeat('0', -$decimalPosition).$digits;
        } elseif ($decimalPosition >= strlen($digits)) {
            $decimal = $digits.str_repeat('0', $decimalPosition - strlen($digits));
        } else {
            $decimal = substr($digits, 0, $decimalPosition).'.'.substr($digits, $decimalPosition);
        }

        return $negative ? '-'.$decimal : $decimal;
    }

    private static function normalizeDecimalString(string $value): string
    {
        if (str_contains($value, '.')) {
            $value = rtrim(rtrim($value, '0'), '.');
        }

        return $value === '-0' || $value === '' ? '0' : $value;
    }
}
