<?php

use Illuminate\Support\Collection;
use Laraveltoolkit\Tests\Enum\FakeInvalidEnum;
use Laraveltoolkit\Tests\Enum\FakeValidEnum;

it('can convert enum into an array', function () {
    expect(FakeValidEnum::toEnumArray())
        ->toBeArray()
        ->toHaveCount(count(FakeValidEnum::cases()))
        ->and(fn() => FakeInvalidEnum::toEnumArray())
        ->toThrow('Laraveltoolkit\Tests\Enum\FakeInvalidEnum is not a valid enum');
});

it('can get label', function (FakeValidEnum $enum) {
    expect($enum->label())
        ->toBeString()
        ->toBe($enum->value);
})->with(FakeValidEnum::cases());

it('can get value label collection', function () {
    expect(FakeValidEnum::toValueLabel())
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(count(FakeValidEnum::cases()))
        ->each
        ->toHaveKeys(['value', 'label'])
        ->and(FakeValidEnum::toValueLabel('foo', 'bar'))
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(count(FakeValidEnum::cases()))
        ->each
        ->toHaveKeys(['bar', 'foo']);
});
