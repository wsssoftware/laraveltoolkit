<?php

it('can apply mask', function () {
    expect(Str::applyMask('12ABCa2c123B4', 'SSS AAA 0000'))
        ->toEqual('ABC a2c 1234')
        ->and($stringable = str('12ABCa2c123B4')->applyMask('SSS AAA 0000'))
        ->toBeInstanceOf(\Illuminate\Support\Stringable::class)
        ->and($stringable->toString())
        ->toEqual('ABC a2c 1234')
        ->and(Str::applyMask(' 12ABCa2c123B4', '\0\A\S SSS AAA 0000'))
        ->toEqual('0AS ABC a2c 1234')
        ->and(Str::applyMask(' 12', '0000'))
        ->toEqual('12');

});

it('can format personal name', function () {
    expect(Str::personalName('ALLAN MARIUCCI DE CARVALHO'))
        ->toEqual('Allan Mariucci de Carvalho')
        ->and(str('ALLAN MARIUCCI DE CARVALHO')->personalName())
        ->toEqual('Allan Mariucci de Carvalho')
        ->and(Str::personalName('luiz ap. de carvalho'))
        ->toEqual('Luiz Ap. de Carvalho')
        ->and(str('Luiz ap. De Carvalho')->personalName())
        ->toEqual('Luiz Ap. de Carvalho')
        ->and(Str::personalName(''))
        ->toEqual('')
        ->and(str('')->personalName())
        ->toEqual('')
        ->and(Str::personalName(null))
        ->toBeNull()
        ->and(str(null)->personalName()->value())
        ->toEqual('');
});
