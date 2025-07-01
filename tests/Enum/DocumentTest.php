<?php

use Laraveltoolkit\Enum\Document;

it('can guess', function () {
    expect(Document::guessType('03143254323'))
        ->toEqual(Document::CPF)
        ->and(Document::guessType('031.432.543-23'))
        ->toEqual(Document::CPF)
        ->and(Document::guessType('12345123000143'))
        ->toEqual(Document::CNPJ)
        ->and(Document::guessType('12.345.123/0001-43'))
        ->toEqual(Document::CNPJ)
        ->and(Document::guessType('213'))
        ->toBeNull()
        ->and(Document::guessType('5435435435543543543543543'))
        ->toBeNull();
});

it('can get check digits', function () {
    expect(Document::CPF->checkDigits('808.643'))
        ->toBe('69')
        ->and(Document::CPF->checkDigits('808.643.330'))
        ->toBe('72')
        ->and(Document::CPF->checkDigits('751.678.140-14'))
        ->toBe('14')
        ->and(Document::CNPJ->checkDigits('54.133.627/0001'))
        ->toBe('40')
        ->and(Document::CNPJ->checkDigits('54.133.627'))
        ->toBe('69')
        ->and(Document::CNPJ->checkDigits('06.064.076/0001-67'))
        ->toBe('67')
        ->and(Document::GENERIC->checkDigits('309.231.240'))
        ->toBe('63')
        ->and(Document::GENERIC->checkDigits('998.508.220-66'))
        ->toBe('66')
        ->and(Document::GENERIC->checkDigits('26.244.177/0001'))
        ->toBe('27')
        ->and(Document::GENERIC->checkDigits('70.413.981/0001-84'))
        ->toBe('84');
});

it('can generate fakes', function () {
    $generic = Document::GENERIC->fake();
    expect(Document::CPF->fake())
        ->toBeString()
        ->toHaveLength(11)
        ->and(Document::CNPJ->fake())
        ->toBeString()
        ->toHaveLength(14)
        ->and($generic)
        ->toBeString()
        ->and(in_array(strlen($generic), [11, 14]))
        ->toBeTrue();
})->repeat(20);

it('can validate', function () {
    expect(Document::CPF->validate('050.504.760-89'))
        ->toBeTrue()
        ->and(Document::CPF->validate('11111111111'))
        ->toBeFalse()
        ->and(Document::CPF->validate('123'))
        ->toBeFalse()
        ->and(Document::CPF->validate('050.504.760-29'))
        ->toBeFalse()
        ->and(Document::CPF->validate('444.760.640-27'))
        ->toBeTrue()
        ->and(Document::CPF->validate('444.760.640-21'))
        ->toBeFalse()
        ->and(Document::CNPJ->validate('11111111111111'))
        ->toBeFalse()
        ->and(Document::CNPJ->validate('111'))
        ->toBeFalse()
        ->and(Document::CNPJ->validate('58.110.408/0001-04'))
        ->toBeTrue()
        ->and(Document::CNPJ->validate('58.110.408/0001-12'))
        ->toBeFalse()
        ->and(Document::CNPJ->validate('80.096.933/0001-63'))
        ->toBeTrue()
        ->and(Document::CNPJ->validate('80.096.933/0001-43'))
        ->toBeFalse()
        ->and(Document::GENERIC->validate('510.403.630-83'))
        ->toBeTrue()
        ->and(Document::GENERIC->validate('510.403.630-81'))
        ->toBeFalse()
        ->and(Document::GENERIC->validate('44.600.135/0001-63'))
        ->toBeTrue()
        ->and(Document::GENERIC->validate('44.600.135/0001-62'))
        ->toBeFalse();
});

it('can unmask', function () {
    expect(Document::CPF->unmask('050.504.760-89'))
        ->toEqual('05050476089')
        ->and(Document::CNPJ->unmask('58.110.408/0001-04'))
        ->toEqual('58110408000104')
        ->and(Document::GENERIC->unmask('58.110.408/0001-04'))
        ->toEqual('58110408000104');
});

it('can mask', function () {
    expect(Document::CPF->mask('05050476089'))
        ->toEqual('050.504.760-89')
        ->and(Document::CNPJ->mask('58110408000104'))
        ->toEqual('58.110.408/0001-04')
        ->and(Document::GENERIC->mask('05050476089'))
        ->toEqual('050.504.760-89')
        ->and(Document::GENERIC->mask('58110408000104'))
        ->toEqual('58.110.408/0001-04');
});

it('can get label', function (Document $document) {
    expect($document->label())
        ->toBeString();
})->with(Document::cases());
