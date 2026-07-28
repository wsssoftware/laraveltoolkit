<?php

use Laraveltoolkit\Measurement\Enums\TemperatureUnit;
use Laraveltoolkit\Measurement\Models\Temperature;

it('describes and resolves localized temperature units', function () {
    expect(TemperatureUnit::CELSIUS->name())->toBe('Celsius')
        ->and(TemperatureUnit::CELSIUS->term())->toBe('°C')
        ->and(TemperatureUnit::FAHRENHEIT->term('en'))->toBe('°F')
        ->and((string) TemperatureUnit::CELSIUS->reference())->toBe('9000000000')
        ->and((string) TemperatureUnit::FAHRENHEIT->reference())->toBe('5000000000')
        ->and(TemperatureUnit::resolve('celsius'))->toBe(TemperatureUnit::CELSIUS)
        ->and(TemperatureUnit::resolve('°C'))->toBe(TemperatureUnit::CELSIUS)
        ->and(TemperatureUnit::resolve('C'))->toBe(TemperatureUnit::CELSIUS)
        ->and(TemperatureUnit::resolve('K'))->toBe(TemperatureUnit::KELVIN)
        ->and(fn () => TemperatureUnit::resolve('warm'))
        ->toThrow(InvalidArgumentException::class, 'Unknown temperature unit [warm].');
});

it('converts absolute temperatures with their offsets', function () {
    expect(temperature(0)->to(TemperatureUnit::FAHRENHEIT)->value())->toBe(32.0)
        ->and(temperature(0)->to(TemperatureUnit::KELVIN)->value())->toBe(273.15)
        ->and(temperature(32, '°F')->to(TemperatureUnit::CELSIUS)->value())->toBe(0.0)
        ->and(temperature(-40, '°C')->equals(temperature(-40, '°F')))->toBeTrue()
        ->and(temperature(0, 'K')->to(TemperatureUnit::RANKINE)->value())->toBe(0.0)
        ->and(temperature(0)->referenceValue())->toBe(2_458_350_000_000);
});

it('applies scalar temperature deltas in the current scale', function () {
    expect(temperature(10, '°C')->add(5)->value())->toBe(15.0)
        ->and(temperature(50, '°F')->add(10)->value())->toBe(60.0)
        ->and(temperature(10, '°C')->sub(5)->value())->toBe(5.0)
        ->and(temperature(10, '°C')->mul(2)->value())->toBe(20.0)
        ->and(temperature(10, '°C')->div(2)->value())->toBe(5.0);
});

it('calculates temperature differences without treating them as absolute values', function () {
    $boiling = temperature(100, '°C');
    $freezing = temperature(32, '°F');

    expect($boiling->difference($freezing))->toBe(100.0)
        ->and($boiling->difference($freezing, TemperatureUnit::FAHRENHEIT))->toBe(180.0)
        ->and(fn () => $boiling->add($freezing))
        ->toThrow(InvalidArgumentException::class, 'Temperature arithmetic accepts scalar deltas');
});

it('formats, serializes, and defaults to Celsius', function () {
    $temperature = temperature(2.5);

    expect($temperature)->toBeInstanceOf(Temperature::class)
        ->and($temperature->unit())->toBe(TemperatureUnit::CELSIUS)
        ->and($temperature->format())->toBe('2,5 °C')
        ->and($temperature->format(short: false))->toBe('2,5 graus celsius')
        ->and(temperature(2, 'C')->format(short: false, locale: 'en'))->toBe('2 degrees celsius')
        ->and(temperature(1, 'K')->format(short: false, locale: 'en'))->toBe('1 kelvin')
        ->and($temperature->toArray())->toBe([
            'value' => 2.5,
            'unit' => 'celsius',
        ]);
});
