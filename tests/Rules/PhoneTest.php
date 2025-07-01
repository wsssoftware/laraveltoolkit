<?php

use Laraveltoolkit\Enum\Phone;
use Laraveltoolkit\Rules\PhoneRule;

it('remove duplicated types', function () {
    $rule = PhoneRule::make(Phone::MOBILE, Phone::MOBILE);
    expect($rule->types)
        ->toHaveCount(1);
});

it('remove generic when has other types', function () {
    $rule = PhoneRule::make(Phone::MOBILE, Phone::LANDLINE, Phone::GENERIC);
    expect($rule->types)
        ->toHaveCount(2)
        ->and($rule->types->first())
        ->toBe(Phone::MOBILE)
        ->and($rule->types->last())
        ->toBe(Phone::LANDLINE);
    $rule2 = PhoneRule::make(Phone::GENERIC, Phone::NON_REGIONAL, Phone::LANDLINE);
    expect($rule2->types)
        ->toHaveCount(2)
        ->and($rule2->types->first())
        ->toBe(Phone::NON_REGIONAL)
        ->and($rule2->types->last())
        ->toBe(Phone::LANDLINE);
});

it('pass on validation from single type', function (Phone $phone) {
    $phoneN = $phone->fake();
    $data = [
        'phone' => $phoneN,
    ];
    $rules = [
        'phone' => PhoneRule::make($phone),
    ];
    $result = Validator::make($data, $rules)->errors();
    expect($result->has('phone'))
        ->toBeFalse($phoneN);
})->with(Phone::cases())
    ->repeat(10);

it('fail on validation from single type', function () {
    $data = [
        'landline' => '43991727288',
        'local_fare' => '50001234',
        'mobile' => '4334723454',
        'non_regional' => '4334721232',
        'public_services' => '200',
        'generic' => '12',
    ];
    $rules = [
        'landline' => PhoneRule::landline(),
        'local_fare' => PhoneRule::localFare(),
        'mobile' => PhoneRule::mobile(),
        'non_regional' => PhoneRule::nonRegional(),
        'public_services' => PhoneRule::publicServices(),
        'generic' => PhoneRule::all(),
    ];
    $result = Validator::make($data, $rules)->errors();
    expect(Arr::get($result->get('landline'), 0))
        ->toEqual('O campo landline não é um telefone fixo válido.')
        ->and(Arr::get($result->get('local_fare'), 0))
        ->toEqual('O campo local fare não é um telefone de tarifa local válido.')
        ->and(Arr::get($result->get('mobile'), 0))
        ->toEqual('O campo mobile não é um celular válido.')
        ->and(Arr::get($result->get('non_regional'), 0))
        ->toEqual('O campo non regional não é um telefone não regional válido.')
        ->and(Arr::get($result->get('public_services'), 0))
        ->toEqual('O campo public services não é um telefone de serviços públicos válido.')
        ->and(Arr::get($result->get('generic'), 0))
        ->toEqual('O campo generic não é um telefone válido.');
});

it('pass on validation from multiple types', function () {
    $data = [
        'phone1' => Phone::MOBILE->fake(),
        'phone2' => Phone::LANDLINE->fake(),
        'phone3' => '190',
        'phone4' => '08001234567',
    ];
    $rules = [
        'phone1' => PhoneRule::make(Phone::LANDLINE, Phone::MOBILE),
        'phone2' => PhoneRule::make(Phone::LANDLINE, Phone::MOBILE),
        'phone3' => PhoneRule::make(Phone::NON_REGIONAL, Phone::PUBLIC_SERVICES),
        'phone4' => PhoneRule::make(Phone::NON_REGIONAL, Phone::PUBLIC_SERVICES),
    ];
    $result = Validator::make($data, $rules)->errors();
    expect($result->has('phone1'))
        ->toBeFalse()
        ->and($result->has('phone2'))
        ->toBeFalse()
        ->and($result->has('phone3'))
        ->toBeFalse()
        ->and($result->has('phone4'))
        ->toBeFalse();
});

it('fail on validation from multiple types', function () {
    $data = [
        'phone1' => Phone::NON_REGIONAL->fake(),
        'phone2' => Phone::PUBLIC_SERVICES->fake(),
        'phone3' => Phone::MOBILE->fake(),
        'phone4' => Phone::LOCAL_FARE->fake(),
    ];
    $rules = [
        'phone1' => PhoneRule::make(Phone::LANDLINE, Phone::MOBILE, Phone::PUBLIC_SERVICES),
        'phone2' => PhoneRule::make(Phone::LANDLINE, Phone::MOBILE),
        'phone3' => PhoneRule::make(Phone::NON_REGIONAL, Phone::PUBLIC_SERVICES),
        'phone4' => PhoneRule::make(Phone::NON_REGIONAL, Phone::PUBLIC_SERVICES),
    ];
    $result = Validator::make($data, $rules)->errors();
    expect(Arr::get($result->get('phone1'), 0))
        ->toEqual('O campo phone1 não é um telefone válido (telefone fixo,celular e telefone de serviços públicos).')
        ->and(Arr::get($result->get('phone2'), 0))
        ->toEqual('O campo phone2 não é um telefone válido (telefone fixo e celular).')
        ->and(Arr::get($result->get('phone3'), 0))
        ->toEqual('O campo phone3 não é um telefone válido (telefone não regional e telefone de serviços públicos).')
        ->and(Arr::get($result->get('phone4'), 0))
        ->toEqual('O campo phone4 não é um telefone válido (telefone não regional e telefone de serviços públicos).');
});
