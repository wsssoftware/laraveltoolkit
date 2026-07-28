<?php

it('can safely load the helpers more than once', function () {
    require dirname(__DIR__).'/src/helpers.php';

    expect([
        'floatToBcNumber',
        'length',
        'weight',
        'volume',
        'area',
        'temperature',
        'speed',
        'duration',
        'pressure',
        'energy',
        'power',
    ])->each->toBeCallable();
});
