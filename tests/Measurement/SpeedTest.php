<?php

use Laraveltoolkit\Measurement\Enums\SpeedUnit;
use Laraveltoolkit\Measurement\Models\Speed;

it('describes and resolves localized speed units', function () {
    expect(SpeedUnit::KILOMETER_PER_HOUR->name())->toBe('Quilômetro por hora')
        ->and(SpeedUnit::KILOMETER_PER_HOUR->term())->toBe('km/h')
        ->and(SpeedUnit::FOOT_PER_SECOND->name('en'))->toBe('Foot per second')
        ->and(SpeedUnit::FOOT_PER_SECOND->name())->toBe('Pé por segundo')
        ->and((string) SpeedUnit::KILOMETER_PER_HOUR->reference())->toBe('1000000000')
        ->and((string) SpeedUnit::MILE_PER_HOUR->reference())->toBe('1609344000')
        ->and(SpeedUnit::resolve('quilômetro por hora'))->toBe(SpeedUnit::KILOMETER_PER_HOUR)
        ->and(SpeedUnit::resolve('km/h'))->toBe(SpeedUnit::KILOMETER_PER_HOUR)
        ->and(SpeedUnit::resolve('ft/s'))->toBe(SpeedUnit::FOOT_PER_SECOND)
        ->and(SpeedUnit::resolve('pé/s'))->toBe(SpeedUnit::FOOT_PER_SECOND)
        ->and(fn () => SpeedUnit::resolve('warp'))
        ->toThrow(InvalidArgumentException::class, 'Unknown speed unit [warp].');
});

it('converts speeds exactly between metric, imperial, and nautical units', function () {
    expect(speed(36)->to(SpeedUnit::METER_PER_SECOND)->value())->toBe(10.0)
        ->and(speed(60, 'mph')->to(SpeedUnit::KILOMETER_PER_HOUR)->value())->toBe(96.56064)
        ->and(speed(1, 'kn')->to(SpeedUnit::KILOMETER_PER_HOUR)->value())->toBe(1.852)
        ->and(speed(1, 'ft/s')->to(SpeedUnit::METER_PER_SECOND)->value())->toBe(0.3048)
        ->and(speed(3.6, 'km/h')->equals(speed(1, 'm/s')))->toBeTrue();
});

it('formats and operates on immutable speeds', function () {
    $speed = speed(50, 'km/h');
    $sum = $speed->add(speed(10, 'm/s'));

    expect($speed->value())->toBe(50.0)
        ->and($sum->value())->toBe(86.0)
        ->and($sum->format())->toBe('86 km/h')
        ->and($sum->format(short: false))->toBe('86 quilômetros por hora')
        ->and(speed(2, 'ft/s')->format(short: false, locale: 'en'))->toBe('2 feet per second');
});

it('serializes a speed and uses kilometers per hour by default', function () {
    $speed = speed(90);

    expect($speed)->toBeInstanceOf(Speed::class)
        ->and($speed->unit())->toBe(SpeedUnit::KILOMETER_PER_HOUR)
        ->and($speed->toArray())->toBe([
            'value' => 90.0,
            'unit' => 'kilometer_per_hour',
        ]);
});
