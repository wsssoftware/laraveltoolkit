<?php

use Laraveltoolkit\Measurement\Enums\EnergyUnit;
use Laraveltoolkit\Measurement\Enums\PowerUnit;
use Laraveltoolkit\Measurement\Models\Energy;

it('describes and resolves localized energy units', function () {
    expect(EnergyUnit::KILOWATT_HOUR->name())->toBe('Quilowatt-hora')
        ->and(EnergyUnit::KILOWATT_HOUR->term())->toBe('kWh')
        ->and(EnergyUnit::BTU->name('en'))->toBe('British thermal unit')
        ->and(EnergyUnit::BTU->name())->toBe('Unidade térmica britânica')
        ->and((string) EnergyUnit::JOULE->reference())->toBe('1000000')
        ->and(EnergyUnit::resolve('quilowatt-hora'))->toBe(EnergyUnit::KILOWATT_HOUR)
        ->and(EnergyUnit::resolve('kWh'))->toBe(EnergyUnit::KILOWATT_HOUR)
        ->and(EnergyUnit::resolve('BTU'))->toBe(EnergyUnit::BTU)
        ->and(fn () => EnergyUnit::resolve('spark'))
        ->toThrow(InvalidArgumentException::class, 'Unknown energy unit [spark].');
});

it('converts electrical, thermal, and metric energy units', function () {
    expect(energy(1, 'kWh')->to(EnergyUnit::JOULE)->value())->toBe(3_600_000.0)
        ->and(energy(1, 'kcal')->to(EnergyUnit::JOULE)->value())->toBe(4184.0)
        ->and(energy(1, 'BTU')->to(EnergyUnit::JOULE)->value())->toBe(1055.055853)
        ->and(energy(1000, 'J')->equals(energy(1, 'kJ')))->toBeTrue();
});

it('calculates power from energy over a duration', function () {
    $power = energy(1, 'kWh')->power(duration(1, 'h'), PowerUnit::KILOWATT);

    expect($power->value())->toBe(1.0)
        ->and(fn () => energy(1, 'J')->power(duration(0)))
        ->toThrow(DivisionByZeroError::class, 'Cannot calculate power from a zero duration.');
});

it('formats, serializes, and defaults to joules', function () {
    $energy = energy(2.5);

    expect($energy)->toBeInstanceOf(Energy::class)
        ->and($energy->unit())->toBe(EnergyUnit::JOULE)
        ->and($energy->format())->toBe('2,5 J')
        ->and($energy->format(short: false))->toBe('2,5 joules')
        ->and($energy->toArray())->toBe([
            'value' => 2.5,
            'unit' => 'joule',
        ]);
});
