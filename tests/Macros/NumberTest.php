<?php

it('can spell', function () {
    expect(Number::spellCurrency(1))
        ->toEqual('um dólar')
        ->and(Number::spellCurrency(2))
        ->toEqual('dois dólares')
        ->and(Number::spellCurrency(2))
        ->toEqual('dois dólares')
        ->and(Number::spellCurrency(0.43))
        ->toEqual('zero dólares e quarenta e três centavos')
        ->and(Number::spellCurrency(1_432_321.52))
        ->toEqual('um milhão quatrocentos e trinta e dois mil trezentos e vinte e um dólares e cinquenta e dois centavos');
});

it('can round as multiple', function () {
    $num1 = Number::roundAsMultipleOf(1.025, 0.01, RoundingMode::HalfAwayFromZero)->value;
    $num2 = Number::roundAsMultipleOf(1.025, 0.01, RoundingMode::HalfTowardsZero)->value;
    $num3 = Number::roundAsMultipleOf(1.025, 0.01, RoundingMode::HalfEven)->value;
    $num4 = Number::roundAsMultipleOf(1.025, 0.01, RoundingMode::HalfOdd)->value;
    $num5 = Number::roundAsMultipleOf(1.025, 0.01, RoundingMode::TowardsZero)->value;
    $num6 = Number::roundAsMultipleOf(1.025, 0.01, RoundingMode::AwayFromZero)->value;
    $num7 = Number::roundAsMultipleOf(1.025, 0.01, RoundingMode::NegativeInfinity)->value;
    $num8 = Number::roundAsMultipleOf(1.025, 0.01, RoundingMode::PositiveInfinity)->value;
    $num9 = Number::roundAsMultipleOf(0.001, 0.01, RoundingMode::HalfAwayFromZero, 0.01)->value;
    $num10 = Number::roundAsMultipleOf(20, 0.01, RoundingMode::PositiveInfinity, max: 10)->value;
    $num11 = Number::roundAsMultipleOf(0.00000000005, 0.0000000001, RoundingMode::HalfAwayFromZero)->value;

    expect($num1)->toEqual('1.03')
        ->and($num2)->toEqual('1.02')
        ->and($num3)->toEqual('1.02')
        ->and($num4)->toEqual('1.03')
        ->and($num5)->toEqual('1.02')
        ->and($num6)->toEqual('1.03')
        ->and($num7)->toEqual('1.02')
        ->and($num8)->toEqual('1.03')
        ->and($num9)->toEqual('0.01')
        ->and($num11)->toEqual('0.0000000001');
});
