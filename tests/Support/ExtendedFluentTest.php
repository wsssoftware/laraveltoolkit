<?php

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Laraveltoolkit\Support\AsExtendedFluent;
use Laraveltoolkit\Support\ExtendedFluent;
use Laraveltoolkit\Tests\Model\User;
use Laraveltoolkit\Tests\Support\FooBarExtendedFluent;
use Laraveltoolkit\Tests\Support\TestEnum;

it('test extended fluent', function () {
    $fluent = new class extends ExtendedFluent {
        protected function fullName(): \Illuminate\Database\Eloquent\Casts\Attribute
        {
            return \Illuminate\Database\Eloquent\Casts\Attribute::make(
                get: fn($value) => $this->name.' '.$this->last_name,
                set: function ($value) {
                    $this->name = explode(' ', $value)[0];
                    $this->last_name = explode(' ', $value)[1];
                },
            );
        }

        protected function enumValue(): \Illuminate\Database\Eloquent\Casts\Attribute
        {
            return \Illuminate\Database\Eloquent\Casts\Attribute::make(
                get: fn($value) => TestEnum::from($value),
            );
        }
    };

    $fluent->enum_value = 'a';
    $fluent->name = 'Paul';
    $fluent->last_name = 'Job';
    $fluent->array_value = collect(['enum' => TestEnum::B]);
    $fluent->array_values = [
        ['enum' => TestEnum::C],
    ];

    expect($fluent->full_name)
        ->toEqual('Paul Job');

    $fluent->full_name = 'Foo Bar';

    $array = $fluent->toArray();
    $storageArray = $fluent->toStorageArray();
    expect($fluent->name)
        ->toEqual('Foo')
        ->and($fluent->last_name)
        ->toEqual('Bar')
        ->and($fluent->enum_value)
        ->toEqual(TestEnum::A)
        ->and($array)
        ->toBeArray()
        ->and($array['enum_value'])
        ->toBeArray()
        ->toHaveKey('id')
        ->and($array['array_value'])
        ->toBeArray()
        ->toHaveKey('enum.id')
        ->and($array['array_values'])
        ->toBeArray()
        ->toHaveKey('0.enum.id')
        ->and($storageArray)
        ->toBeArray()
        ->and($storageArray['enum_value'])
        ->toBe('a')
        ->and($storageArray['array_value'])
        ->toBeArray()
        ->and($storageArray['array_value']['enum'])
        ->toBe('b')
        ->and($storageArray['array_values'])
        ->toBeArray()
        ->and($storageArray['array_values'][0]['enum'])
        ->toBe('c');
});

it('test extended casts', function () {

    $cast = AsExtendedFluent::castUsing([FooBarExtendedFluent::class]);
    $model = new User;

    expect(AsExtendedFluent::of(FooBarExtendedFluent::class))
        ->toEqual(AsExtendedFluent::class.':'.FooBarExtendedFluent::class)
        ->and(fn() => AsExtendedFluent::castUsing(['invalid_class']))
        ->toThrow('Class invalid_class does not exist.')
        ->and(fn() => AsExtendedFluent::castUsing([AsExtendedFluent::class]))
        ->toThrow('Class Laraveltoolkit\Support\AsExtendedFluent does not extend ExtendedFluent.')
        ->and($cast)
        ->toBeInstanceOf(CastsAttributes::class)
        ->and($cast->get($model, 'foo', '----', []))
        ->toBe('----')
        ->and($cast->get($model, 'foo', '{"id": 2}', []))
        ->toBeInstanceOf(FooBarExtendedFluent::class)
        ->and($cast->get($model, 'foo', '{"id": 2}', []))
        ->toBeInstanceOf(FooBarExtendedFluent::class)
        ->and($cast->set($model, 'foo', new FooBarExtendedFluent(['id' => 2]), []))
        ->toBeJson()
        ->and($cast->set($model, 'foo', 'invalid', []))
        ->toBe('invalid');
});
