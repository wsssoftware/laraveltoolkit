<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;

it('can generate column', function () {
    $blueprint = app()->make(Blueprint::class, ['table' => 'foo']);
    $column = $blueprint->storedAsset('foo_image');
    expect($column)
        ->toBeInstanceOf(ColumnDefinition::class)
        ->and($column->get('type'))
        ->toBe('uuid')
        ->and($column->get('name'))
        ->toBe('foo_image');
});

it('can generate a measurement column with regular column modifiers', function () {
    $blueprint = app()->make(Blueprint::class, ['table' => 'foo']);
    $column = $blueprint->measurement('distance')->nullable();

    expect($column)
        ->toBeInstanceOf(ColumnDefinition::class)
        ->and($column->get('type'))->toBe('json')
        ->and($column->get('name'))->toBe('distance')
        ->and($column->get('nullable'))->toBeTrue();
});
