<?php

use Laraveltoolkit\Facades\Flash;

it('can get', function () {

    Flash::info('ok');
    Flash::success('ok');

    $response = $this->get(route('lt.flash.get_messages'));

    $response->assertSuccessful();

    expect($response->json())
        ->toBeArray()
        ->each
        ->toHaveKeys(['severity', 'detail', 'group']);

});
