<?php

use Laraveltoolkit\Measurement\Enums\DurationUnit;
use Laraveltoolkit\Measurement\Models\Duration;

it('describes and resolves localized duration units', function () {
    expect(DurationUnit::SECOND->name())->toBe('Segundo')
        ->and(DurationUnit::SECOND->term())->toBe('s')
        ->and(DurationUnit::WEEK->name('en'))->toBe('Week')
        ->and(DurationUnit::WEEK->term())->toBe('sem')
        ->and((string) DurationUnit::SECOND->reference())->toBe('1000000000')
        ->and(DurationUnit::resolve('segundo'))->toBe(DurationUnit::SECOND)
        ->and(DurationUnit::resolve('s'))->toBe(DurationUnit::SECOND)
        ->and(DurationUnit::resolve('wk'))->toBe(DurationUnit::WEEK)
        ->and(DurationUnit::resolve('mês'))->toBe(DurationUnit::MONTH)
        ->and(DurationUnit::resolve('yr'))->toBe(DurationUnit::YEAR)
        ->and(fn () => DurationUnit::resolve('forever'))
        ->toThrow(InvalidArgumentException::class, 'Unknown duration unit [forever].');
});

it('converts durations exactly', function () {
    expect(duration(1, 'h')->to(DurationUnit::MINUTE)->value())->toBe(60.0)
        ->and(duration(1, 'd')->to(DurationUnit::HOUR)->value())->toBe(24.0)
        ->and(duration(1, 'wk')->to(DurationUnit::DAY)->value())->toBe(7.0)
        ->and(duration(1, 'ano')->to(DurationUnit::DAY)->value())->toBe(365.25)
        ->and(duration(1, 'mês')->to(DurationUnit::DAY)->value())->toBe(30.4375)
        ->and(duration(1000, 'ms')->equals(duration(1, 's')))->toBeTrue();
});

it('supports durations whose canonical value exceeds the native integer range', function () {
    $duration = duration(1000, DurationUnit::YEAR);

    expect($duration->referenceValue())->toBe('31557600000000000000')
        ->and($duration->to(DurationUnit::MONTH)->value())->toBe(12_000.0);
});

it('formats and operates on immutable durations', function () {
    $duration = duration(1, 'h');
    $sum = $duration->add(duration(30, 'min'));

    expect($duration->value())->toBe(1.0)
        ->and($sum->value())->toBe(1.5)
        ->and($sum->format())->toBe('1,5 h')
        ->and($sum->format(short: false))->toBe('1,5 horas')
        ->and(duration(2, 'd')->format(short: false, locale: 'en'))->toBe('2 days');
});

it('serializes a duration and uses seconds by default', function () {
    $duration = duration(2.5);

    expect($duration)->toBeInstanceOf(Duration::class)
        ->and($duration->unit())->toBe(DurationUnit::SECOND)
        ->and($duration->toArray())->toBe([
            'value' => 2.5,
            'unit' => 'second',
        ]);
});
