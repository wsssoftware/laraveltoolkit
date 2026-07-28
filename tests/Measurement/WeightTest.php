<?php

use Laraveltoolkit\Measurement\Enums\WeightUnit;
use Laraveltoolkit\Measurement\Models\Weight;

it('describes and resolves localized weight units', function () {
    expect(WeightUnit::KILOGRAM->name())->toBe('Quilograma')
        ->and(WeightUnit::KILOGRAM->term())->toBe('kg')
        ->and(WeightUnit::POUND->name('en'))->toBe('Pound')
        ->and(WeightUnit::POUND->name())->toBe('Libra')
        ->and((string) WeightUnit::KILOGRAM->reference())->toBe('1000000000000')
        ->and((string) WeightUnit::POUND->reference())->toBe('453592370000')
        ->and(WeightUnit::resolve('quilograma'))->toBe(WeightUnit::KILOGRAM)
        ->and(WeightUnit::resolve('kg'))->toBe(WeightUnit::KILOGRAM)
        ->and(WeightUnit::resolve('pound'))->toBe(WeightUnit::POUND)
        ->and(WeightUnit::resolve('lb'))->toBe(WeightUnit::POUND)
        ->and(fn () => WeightUnit::resolve('potato'))
        ->toThrow(InvalidArgumentException::class, 'Unknown weight unit [potato].');
});

it('converts weights exactly between metric and imperial units', function () {
    expect(weight(1)->to(WeightUnit::GRAM)->value())->toBe(1000.0)
        ->and(weight(1, 'lb')->to(WeightUnit::KILOGRAM)->value())->toBe(0.45359237)
        ->and(weight(1, 'st')->to(WeightUnit::POUND)->value())->toBe(14.0)
        ->and(weight(16, 'oz')->equals(weight(1, 'lb')))->toBeTrue();
});

it('formats and operates on immutable weights', function () {
    $weight = weight(1, 'kg');
    $sum = $weight->add(weight(500, 'g'));

    expect($weight->value())->toBe(1.0)
        ->and($sum->value())->toBe(1.5)
        ->and($sum->format())->toBe('1,5 kg')
        ->and($sum->format(short: false))->toBe('1,5 quilogramas')
        ->and(weight(2, 'lb')->format(short: false, locale: 'en'))->toBe('2 pounds')
        ->and(weight(1, 'lb')->referenceValue())->toBe(453_592_370_000);
});

it('serializes a weight and uses kilograms by default', function () {
    $weight = weight(2.5);

    expect($weight)->toBeInstanceOf(Weight::class)
        ->and($weight->unit())->toBe(WeightUnit::KILOGRAM)
        ->and($weight->toArray())->toBe([
            'value' => 2.5,
            'unit' => 'kilogram',
        ]);
});
