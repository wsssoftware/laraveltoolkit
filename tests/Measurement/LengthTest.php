<?php

use Laraveltoolkit\Measurement\Enums\LengthUnit;
use Laraveltoolkit\Measurement\Models\Length;
use Laraveltoolkit\Measurement\Models\Number;

it('describes each length unit', function () {
    expect(LengthUnit::CENTIMETER->name())->toBe('Centímetro')
        ->and(LengthUnit::CENTIMETER->term())->toBe('cm')
        ->and(LengthUnit::INCH->name())->toBe('Polegada')
        ->and(LengthUnit::INCH->term())->toBe('pol')
        ->and((string) LengthUnit::CENTIMETER->reference())->toBe('10000000')
        ->and((string) LengthUnit::METER->reference())->toBe('1000000000');
});

it('translates length unit names and terms using the application locale', function () {
    app()->setLocale('en');

    expect(LengthUnit::CENTIMETER->name())->toBe('Centimeter')
        ->and(LengthUnit::CENTIMETER->term())->toBe('cm')
        ->and(LengthUnit::INCH->name())->toBe('Inch')
        ->and(LengthUnit::INCH->term())->toBe('in');
});

it('formats a length with localized short and full postfixes', function () {
    $length = length(1234.5, LengthUnit::INCH);

    expect($length->format())->toBe('1.234,5 pol')
        ->and($length->format(precision: 2))->toBe('1.234,50 pol')
        ->and($length->format(locale: 'en'))->toBe('1,234.5 in')
        ->and($length->format(null, null, false, 'en'))->toBe('1,234.5 inches')
        ->and($length->format(locale: 'pt_BR', short: false))->toBe('1.234,5 polegadas')
        ->and($length->format(locale: 'en', short: false))->toBe('1,234.5 inches');
});

it('pluralizes lowercase full postfixes based on the displayed value', function () {
    expect(length(1, 'm')->format(short: false))->toBe('1 metro')
        ->and(length(-1, 'm')->format(short: false))->toBe('-1 metro')
        ->and(length(2, 'm')->format(short: false))->toBe('2 metros')
        ->and(length(1.4, 'm')->format(short: false))->toBe('1,4 metros')
        ->and(length(1.4, 'm')->format(precision: 0, short: false))->toBe('1 metro')
        ->and(length(1, 'ft')->format(short: false, locale: 'en'))->toBe('1 foot')
        ->and(length(2, 'ft')->format(short: false, locale: 'en'))->toBe('2 feet');
});

it('formats a length using the maximum precision behavior from Laravel', function () {
    $length = length(1234.567, LengthUnit::CENTIMETER);

    expect($length->format(maxPrecision: 2))->toBe('1.234,57 cm')
        ->and($length->format(precision: 4, maxPrecision: 2))->toBe('1.234,57 cm')
        ->and($length->formatWithoutUnit(maxPrecision: 2))->toBe('1.234,57')
        ->and($length->formatWithoutUnit(precision: 2, locale: 'en'))->toBe('1,234.57');
});

it('returns explicitly typed and rounded display values', function () {
    $length = length(12.55, LengthUnit::CENTIMETER);

    expect($length->toFloat())->toBe(12.55)
        ->and($length->toInteger())->toBe(13)
        ->and($length->toInteger(RoundingMode::TowardsZero))->toBe(12)
        ->and($length->round())->toBe(13.0)
        ->and($length->round(1, RoundingMode::HalfEven))->toBe(12.6)
        ->and(length(PHP_INT_MAX, 'm')->toInteger())->toBe(PHP_INT_MAX)
        ->and(fn () => length(PHP_INT_MAX, 'm')->add(1)->toInteger())
        ->toThrow(OverflowException::class, 'exceeds the native integer range')
        ->and(fn () => length(PHP_INT_MIN, 'm')->sub(1)->toInteger())
        ->toThrow(OverflowException::class, 'exceeds the native integer range');
});

it('resolves units from names, values, and terms', function () {
    expect(LengthUnit::resolve('CENTIMETER'))->toBe(LengthUnit::CENTIMETER)
        ->and(LengthUnit::resolve('centimeter'))->toBe(LengthUnit::CENTIMETER)
        ->and(LengthUnit::resolve('centímetro'))->toBe(LengthUnit::CENTIMETER)
        ->and(LengthUnit::resolve('cm'))->toBe(LengthUnit::CENTIMETER)
        ->and(LengthUnit::resolve('in'))->toBe(LengthUnit::INCH)
        ->and(LengthUnit::resolve('pol'))->toBe(LengthUnit::INCH)
        ->and(fn () => LengthUnit::resolve('potato'))
        ->toThrow(InvalidArgumentException::class, 'Unknown length unit [potato].');
});

it('converts lengths between metric and imperial units', function () {
    $meters = new Length(1, LengthUnit::METER);

    expect($meters->to(LengthUnit::CENTIMETER)->value())->toBe(100.0)
        ->and($meters->to(LengthUnit::MILLIMETER)->value())->toBe(1000.0)
        ->and(length(2.54, 'cm')->to(LengthUnit::INCH)->value())->toBe(1.0)
        ->and(length(1, 'mi')->to(LengthUnit::KILOMETER)->value())->toBe(1.609344);
});

it('stores exact lengths as canonical nanometers', function () {
    $centimeters = length(2.54, 'cm');
    $inch = length(1, 'in');

    expect($centimeters->referenceValue())->toBe(25_400_000)
        ->and($inch->referenceValue())->toBe(25_400_000)
        ->and($centimeters->equals($inch))->toBeTrue();
});

it('performs immutable arithmetic in the current unit', function () {
    $length = length(1, LengthUnit::METER);

    $sum = $length->add(length(50, LengthUnit::CENTIMETER));
    $difference = $sum->sub(0.25);
    $product = $difference->mul(2);
    $quotient = $product->div(5);

    expect($length->value())->toBe(1.0)
        ->and($sum->value())->toBe(1.5)
        ->and($difference->value())->toBe(1.25)
        ->and($product->value())->toBe(2.5)
        ->and($quotient->value())->toBe(0.5)
        ->and($quotient->unit)->toBe(LengthUnit::METER);
});

it('compares equivalent lengths', function () {
    expect(length(1, 'm')->equals(length(100, 'cm')))->toBeTrue()
        ->and(length(1, 'm')->compare(length(99, 'cm')))->toBe(1)
        ->and(length(1, 'm')->compare(length(101, 'cm')))->toBe(-1);
});

it('serializes a length without requiring a string representation', function () {
    $length = length(12.5, 'cm');

    expect($length->toArray())->toBe([
        'value' => 12.5,
        'unit' => 'centimeter',
    ])->and(json_encode($length, JSON_THROW_ON_ERROR))->toBe('{"value":12.5,"unit":"centimeter"}');
});

it('uses the package number model without a string contract', function () {
    $length = length(1);

    expect($length)->toBeInstanceOf(Number::class)
        ->not->toBeInstanceOf(Stringable::class)
        ->and($length->value())->toBeFloat()
        ->and($length->referenceValue())->toBeInt();
});

it('preserves fractions below the reference unit and rounds only at the internal scale', function () {
    $awayFromZero = length(0.5, 'nm');
    $halfEven = new Length(0.5, LengthUnit::NANOMETER, RoundingMode::HalfEven);

    expect($awayFromZero->referenceValue())->toBe(0.5)
        ->and($halfEven->referenceValue())->toBe(0.5)
        ->and(length(1, 'nm')->div(2)->referenceValue())->toBe(0.5)
        ->and(length(2, 'nm')->div(3)->referenceValueString())
        ->toBe('0.666666666666666666666667')
        ->and(length(2, 'nm')->div(3, RoundingMode::TowardsZero)->referenceValueString())
        ->toBe('0.666666666666666666666666');
});

it('supports canonical values beyond the native integer range', function () {
    expect(length(PHP_INT_MAX, 'm')->referenceValue())
        ->toBe('9223372036854775807000000000')
        ->and(length(PHP_INT_MIN, 'nm')->div(-1)->referenceValue())
        ->toBe('9223372036854775808');
});

it('normalizes finite floats written in scientific notation', function () {
    expect(length(1e20, 'nm')->referenceValueString())->toBe('100000000000000000000')
        ->and(length(1e-20, 'nm')->referenceValueString())->toBe('0.00000000000000000001')
        ->and(length(-1e-20, 'nm')->referenceValueString())->toBe('-0.00000000000000000001');
});

it('rejects invalid arithmetic operands', function () {
    expect(fn () => length(1)->div(0))
        ->toThrow(DivisionByZeroError::class, 'Division by zero')
        ->and(fn () => new Length(INF, LengthUnit::METER))
        ->toThrow(OverflowException::class, 'requires a finite numeric value')
        ->and(fn () => temperature(1)->div(0))
        ->toThrow(DivisionByZeroError::class, 'Division by zero');
});
