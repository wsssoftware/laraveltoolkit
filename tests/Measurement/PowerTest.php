<?php

use Laraveltoolkit\Measurement\Enums\EnergyUnit;
use Laraveltoolkit\Measurement\Enums\PowerUnit;
use Laraveltoolkit\Measurement\Models\Power;

it('describes and resolves localized power units', function () {
    expect(PowerUnit::KILOWATT->name())->toBe('Quilowatt')
        ->and(PowerUnit::KILOWATT->term())->toBe('kW')
        ->and(PowerUnit::MECHANICAL_HORSEPOWER->name('en'))->toBe('Mechanical horsepower')
        ->and(PowerUnit::METRIC_HORSEPOWER->term())->toBe('cv')
        ->and((string) PowerUnit::WATT->reference())->toBe('1000000')
        ->and(PowerUnit::resolve('quilowatt'))->toBe(PowerUnit::KILOWATT)
        ->and(PowerUnit::resolve('kW'))->toBe(PowerUnit::KILOWATT)
        ->and(PowerUnit::resolve('cv'))->toBe(PowerUnit::METRIC_HORSEPOWER)
        ->and(fn () => PowerUnit::resolve('flux'))
        ->toThrow(InvalidArgumentException::class, 'Unknown power unit [flux].');
});

it('converts metric, mechanical, and thermal power units', function () {
    expect(power(1, 'kW')->to(PowerUnit::WATT)->value())->toBe(1000.0)
        ->and(power(1, 'hp')->to(PowerUnit::WATT)->value())->toBe(745.699872)
        ->and(power(1, 'cv')->to(PowerUnit::WATT)->value())->toBe(735.49875)
        ->and(power(1, 'BTU/h')->to(PowerUnit::WATT)->value())->toBe(0.293071);
});

it('calculates energy from power over a duration', function () {
    $energy = power(2, 'kW')->energy(duration(30, 'min'), EnergyUnit::KILOWATT_HOUR);

    expect($energy->value())->toBe(1.0);
});

it('formats, serializes, and defaults to watts', function () {
    $power = power(2.5);

    expect($power)->toBeInstanceOf(Power::class)
        ->and($power->unit())->toBe(PowerUnit::WATT)
        ->and($power->format())->toBe('2,5 W')
        ->and($power->format(short: false))->toBe('2,5 watts')
        ->and($power->toArray())->toBe([
            'value' => 2.5,
            'unit' => 'watt',
        ]);
});
