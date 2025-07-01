<?php

it('can generate column', function () {
    $route = Route::getAndPost('/', function () {});
    expect($route)
        ->toBeInstanceOf(Illuminate\Routing\Route::class)
        ->and($route->methods())
        ->toContain('HEAD', 'GET', 'POST');
});
