<?php

use Laraveltoolkit\Measurement\Enums\AreaUnit;
use Laraveltoolkit\Measurement\Models\Area;

it('describes and resolves localized area units', function () {
    expect(AreaUnit::SQUARE_METER->name())->toBe('Metro quadrado')
        ->and(AreaUnit::SQUARE_METER->term())->toBe('m²')
        ->and(AreaUnit::SQUARE_FOOT->name('en'))->toBe('Square foot')
        ->and(AreaUnit::SQUARE_FOOT->name())->toBe('Pé quadrado')
        ->and((string) AreaUnit::SQUARE_METER->reference())->toBe('100000000')
        ->and((string) AreaUnit::ACRE->reference())->toBe('404685642240')
        ->and(AreaUnit::resolve('metro quadrado'))->toBe(AreaUnit::SQUARE_METER)
        ->and(AreaUnit::resolve('m²'))->toBe(AreaUnit::SQUARE_METER)
        ->and(AreaUnit::resolve('m2'))->toBe(AreaUnit::SQUARE_METER)
        ->and(AreaUnit::resolve('ft²'))->toBe(AreaUnit::SQUARE_FOOT)
        ->and(AreaUnit::resolve('pé²'))->toBe(AreaUnit::SQUARE_FOOT)
        ->and(fn () => AreaUnit::resolve('field'))
        ->toThrow(InvalidArgumentException::class, 'Unknown area unit [field].');
});

it('converts areas exactly between metric and imperial units', function () {
    expect(area(1)->to(AreaUnit::SQUARE_CENTIMETER)->value())->toBe(10_000.0)
        ->and(area(1, 'ha')->to(AreaUnit::SQUARE_METER)->value())->toBe(10_000.0)
        ->and(area(1, 'ac')->to(AreaUnit::SQUARE_FOOT)->value())->toBe(43_560.0)
        ->and(area(1, 'mi²')->to(AreaUnit::ACRE)->value())->toBe(640.0)
        ->and(area(144, 'in²')->equals(area(1, 'ft²')))->toBeTrue();
});

it('formats and operates on immutable areas', function () {
    $area = area(1, 'm²');
    $sum = $area->add(area(5_000, 'cm²'));

    expect($area->value())->toBe(1.0)
        ->and($sum->value())->toBe(1.5)
        ->and($sum->format())->toBe('1,5 m²')
        ->and($sum->format(short: false))->toBe('1,5 metros quadrados')
        ->and(area(2, 'ft²')->format(short: false, locale: 'en'))->toBe('2 square feet');
});

it('serializes an area and uses square meters by default', function () {
    $area = area(2.5);

    expect($area)->toBeInstanceOf(Area::class)
        ->and($area->unit())->toBe(AreaUnit::SQUARE_METER)
        ->and($area->toArray())->toBe([
            'value' => 2.5,
            'unit' => 'square_meter',
        ]);
});
