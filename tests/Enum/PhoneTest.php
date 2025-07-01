<?php

use Laraveltoolkit\Enum\Phone;

it('can guess from number', function () {
    expect(Phone::guessType('4132324545'))
        ->toBe(Phone::LANDLINE)
        ->and(Phone::guessType('40031234'))
        ->toBe(Phone::LOCAL_FARE)
        ->and(Phone::guessType('32988232323'))
        ->toBe(Phone::MOBILE)
        ->and(Phone::guessType('08001235432'))
        ->toBe(Phone::NON_REGIONAL)
        ->and(Phone::guessType('123'))
        ->toBe(Phone::PUBLIC_SERVICES)
        ->and(fn () => Phone::GENERIC->appearsToBe('213'))
        ->toThrow('Appears to be method is not allowed to generic type');

});

it('can validate', function () {
    expect(Phone::LANDLINE->validate('4132324545'))
        ->toBeTrue()
        ->and(Phone::LANDLINE->validate('4192324545'))
        ->toBeFalse()
        ->and(Phone::LOCAL_FARE->validate('40031234'))
        ->toBeTrue()
        ->and(Phone::LOCAL_FARE->validate('39991234'))
        ->toBeFalse()
        ->and(Phone::MOBILE->validate('32988232323'))
        ->toBeTrue()
        ->and(Phone::MOBILE->validate('32948232323'))
        ->toBeFalse()
        ->and(Phone::NON_REGIONAL->validate('08001235432'))
        ->toBeTrue()
        ->and(Phone::NON_REGIONAL->validate('00001235432'))
        ->toBeFalse()
        ->and(Phone::PUBLIC_SERVICES->validate('123'))
        ->toBeTrue()
        ->and(Phone::PUBLIC_SERVICES->validate('1223'))
        ->toBeFalse()
        ->and(Phone::GENERIC->validate('4132324545'))
        ->toBeTrue()
        ->and(Phone::GENERIC->validate('4192324545'))
        ->toBeFalse()
        ->and(Phone::GENERIC->validate('40031234'))
        ->toBeTrue()
        ->and(Phone::GENERIC->validate('39991234'))
        ->toBeFalse()
        ->and(Phone::GENERIC->validate('32988232323'))
        ->toBeTrue()
        ->and(Phone::GENERIC->validate('32948232323'))
        ->toBeFalse()
        ->and(Phone::GENERIC->validate('08001235432'))
        ->toBeTrue()
        ->and(Phone::GENERIC->validate('00001235432'))
        ->toBeFalse()
        ->and(Phone::GENERIC->validate('123'))
        ->toBeTrue()
        ->and(Phone::GENERIC->validate('1223'))
        ->toBeFalse()
        ->and(Phone::GENERIC->validate('1'))
        ->toBeFalse();

});

it('can mask', function () {
    expect(Phone::LANDLINE->mask('4132324545'))
        ->toEqual('(41) 3232-4545')
        ->and(Phone::LOCAL_FARE->mask('40012132'))
        ->toEqual('4001-2132')
        ->and(Phone::MOBILE->mask('43988762432'))
        ->toEqual('(43) 9 8876-2432')
        ->and(Phone::NON_REGIONAL->mask('08003221234'))
        ->toEqual('0800-322-1234')
        ->and(Phone::PUBLIC_SERVICES->mask('180'))
        ->toEqual('180')
        ->and(Phone::GENERIC->mask('4132324545'))
        ->toEqual('(41) 3232-4545')
        ->and(Phone::GENERIC->mask('40012132'))
        ->toEqual('4001-2132')
        ->and(Phone::GENERIC->mask('43988762432'))
        ->toEqual('(43) 9 8876-2432')
        ->and(Phone::GENERIC->mask('180'))
        ->toEqual('180')
        ->and(Phone::GENERIC->mask('1'))
        ->toEqual('1');
});

it('can unmask', function () {
    expect(Phone::LANDLINE->unmask('(41) 3232-4545'))
        ->toEqual('4132324545')
        ->and(Phone::LOCAL_FARE->unmask('4001-2132'))
        ->toEqual('40012132')
        ->and(Phone::MOBILE->unmask('(43) 9 8876-2432'))
        ->toEqual('43988762432')
        ->and(Phone::NON_REGIONAL->unmask('0800-322-1234'))
        ->toEqual('08003221234')
        ->and(Phone::PUBLIC_SERVICES->unmask('180'))
        ->toEqual('180')
        ->and(Phone::GENERIC->unmask('(41) 3232-4545'))
        ->toEqual('4132324545')
        ->and(Phone::GENERIC->unmask('4001-2132'))
        ->toEqual('40012132')
        ->and(Phone::GENERIC->unmask('(43) 9 8876-2432'))
        ->toEqual('43988762432')
        ->and(Phone::GENERIC->unmask('180'))
        ->toEqual('180')
        ->and(Phone::GENERIC->unmask('1'))
        ->toEqual('1');
});

it('can get label', function (Phone $phone) {
    expect($phone->label())
        ->toBeString();
})->with(Phone::cases());

it('can create a fake', function (Phone $phone) {
    expect($phone->validate($phone->fake()))
        ->toBeTrue();
})->with(Phone::cases())
    ->repeat(4);

it('can create a fake generic', function () {
    expect(Phone::GENERIC->validate(Phone::GENERIC->fake()))
        ->toBeTrue();
})->repeat(20);
