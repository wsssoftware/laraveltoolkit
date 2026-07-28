<?php

use Laraveltoolkit\Measurement\Enums\PressureUnit;
use Laraveltoolkit\Measurement\Models\Pressure;

it('describes and resolves localized pressure units', function () {
    expect(PressureUnit::KILOPASCAL->name())->toBe('Quilopascal')
        ->and(PressureUnit::KILOPASCAL->term())->toBe('kPa')
        ->and(PressureUnit::INCH_OF_MERCURY->name('en'))->toBe('Inch of mercury')
        ->and(PressureUnit::INCH_OF_MERCURY->term())->toBe('polHg')
        ->and((string) PressureUnit::PASCAL->reference())->toBe('1000000000')
        ->and(PressureUnit::resolve('quilopascal'))->toBe(PressureUnit::KILOPASCAL)
        ->and(PressureUnit::resolve('kPa'))->toBe(PressureUnit::KILOPASCAL)
        ->and(PressureUnit::resolve('inHg'))->toBe(PressureUnit::INCH_OF_MERCURY)
        ->and(fn () => PressureUnit::resolve('squeeze'))
        ->toThrow(InvalidArgumentException::class, 'Unknown pressure unit [squeeze].');
});

it('converts metric, atmospheric, and imperial pressures', function () {
    expect(pressure(1, 'bar')->to(PressureUnit::KILOPASCAL)->value())->toBe(100.0)
        ->and(pressure(1, 'atm')->to(PressureUnit::KILOPASCAL)->value())->toBe(101.325)
        ->and(pressure(1, 'psi')->to(PressureUnit::KILOPASCAL)->value())->toBe(6.894757293168)
        ->and(pressure(1, 'hPa')->equals(pressure(1, 'mbar')))->toBeTrue();
});

it('formats and operates on immutable pressures', function () {
    $pressure = pressure(100, 'kPa');
    $sum = $pressure->add(pressure(1, 'bar'));

    expect($pressure->value())->toBe(100.0)
        ->and($sum->value())->toBe(200.0)
        ->and($sum->format())->toBe('200 kPa')
        ->and($sum->format(short: false))->toBe('200 quilopascals')
        ->and(pressure(2, 'psi')->format(short: false, locale: 'en'))->toBe('2 pounds per square inch');
});

it('serializes a pressure and uses pascals by default', function () {
    $pressure = pressure(2.5);

    expect($pressure)->toBeInstanceOf(Pressure::class)
        ->and($pressure->unit())->toBe(PressureUnit::PASCAL)
        ->and($pressure->toArray())->toBe([
            'value' => 2.5,
            'unit' => 'pascal',
        ]);
});
