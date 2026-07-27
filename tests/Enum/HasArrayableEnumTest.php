<?php

use Illuminate\Support\Collection;
use Laraveltoolkit\Tests\Enum\FakeArrayableEnum;
use Laraveltoolkit\Tests\Enum\FakeInvalidEnum;
use Laraveltoolkit\Tests\Enum\FakeSortableEnum;
use Laraveltoolkit\Tests\Enum\FakeValidEnum;

it('can convert enum into an array', function () {
    expect(FakeValidEnum::toEnumArray())
        ->toBeArray()
        ->toHaveCount(count(FakeValidEnum::cases()))
        ->and(fn () => FakeInvalidEnum::toEnumArray())
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

it('can convert an arrayable enum into an array', function () {
    expect(FakeArrayableEnum::toEnumArrayable())
        ->toBeArray()
        ->toHaveCount(count(FakeArrayableEnum::cases()))
        ->toBe([
            ['label' => 'Alpha', 'name' => 'Second'],
            ['label' => 'Mike', 'name' => 'Third'],
            ['label' => 'Zulu', 'name' => 'First'],
        ])
        ->and(FakeArrayableEnum::toEnumArrayable(sortFlags: null))
        ->toBe([
            ['label' => 'Zulu', 'name' => 'First'],
            ['label' => 'Alpha', 'name' => 'Second'],
            ['label' => 'Mike', 'name' => 'Third'],
        ]);
});

it('requires an arrayable enum to get its array payload', function () {
    expect(fn () => FakeValidEnum::toEnumArrayable())
        ->toThrow(FakeValidEnum::class.' must implement Illuminate\Contracts\Support\Arrayable');
});

it('can filter enum array by value', function () {
    expect(FakeValidEnum::toEnumArray(only: ['option_1', 'option_3']))
        ->toBe([
            'option_1' => 'option_1',
            'option_3' => 'option_3',
        ])
        ->and(FakeValidEnum::toEnumArray(except: ['option_2', 'option_4']))
        ->toBe([
            'option_1' => 'option_1',
            'option_3' => 'option_3',
        ]);
});

it('can sort enum array by label', function () {
    expect(FakeSortableEnum::toEnumArray())
        ->toBe([
            'second' => 'Beta',
            'first' => 'Alpha',
            'third' => 'Gamma',
        ])
        ->and(FakeSortableEnum::toEnumArray(SORT_REGULAR))
        ->toBe([
            'first' => 'Alpha',
            'second' => 'Beta',
            'third' => 'Gamma',
        ])
        ->and(FakeSortableEnum::toEnumArray(SORT_REGULAR, SORT_DESC))
        ->toBe([
            'third' => 'Gamma',
            'second' => 'Beta',
            'first' => 'Alpha',
        ]);
});

it('can filter and sort value label collection', function () {
    expect(FakeSortableEnum::toValueLabel(sortFlags: null, only: ['second', 'first'])->all())
        ->toBe([
            ['value' => 'second', 'label' => 'Beta'],
            ['value' => 'first', 'label' => 'Alpha'],
        ])
        ->and(FakeSortableEnum::toValueLabel(only: ['second', 'first'])->all())
        ->toBe([
            ['value' => 'first', 'label' => 'Alpha'],
            ['value' => 'second', 'label' => 'Beta'],
        ]);
});

it('can filter and sort an arrayable enum by a payload key', function () {
    expect(FakeArrayableEnum::toEnumArrayable(sortFlags: SORT_REGULAR, only: ['third', 'first']))
        ->toBe([
            ['label' => 'Mike', 'name' => 'Third'],
            ['label' => 'Zulu', 'name' => 'First'],
        ])
        ->and(FakeArrayableEnum::toEnumArrayable('name', SORT_REGULAR, SORT_DESC, except: ['second']))
        ->toBe([
            ['label' => 'Mike', 'name' => 'Third'],
            ['label' => 'Zulu', 'name' => 'First'],
        ]);
});
