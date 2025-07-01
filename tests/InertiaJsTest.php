<?php

use Illuminate\Http\Request;
use Laraveltoolkit\Inertia\HandleInertiaCrossDomainVisits;

const CONTENT = 'used next';

$successClosure = fn() => new \Symfony\Component\HttpFoundation\Response(CONTENT);

it('pass on get visit on same host and using inertia header', function () use ($successClosure) {
    $url = 'http://localhost';
    $request = Request::create($url);
    $request->headers->set('X-Inertia', true);

    /** @var \Laraveltoolkit\Inertia\HandleInertiaCrossDomainVisits $middleware */
    $middleware = app(HandleInertiaCrossDomainVisits::class);

    $response = $middleware->handle($request, $successClosure);
    expect($response->getContent())
        ->toBe(CONTENT);

});

it('has inertia header missing', function () use ($successClosure) {
    $url = 'http://localhost';
    $request = Request::create($url);

    /** @var \Laraveltoolkit\Inertia\HandleInertiaCrossDomainVisits $middleware */
    $middleware = app(HandleInertiaCrossDomainVisits::class);

    $response = $middleware->handle($request, $successClosure);
    expect($response->getContent())
        ->toBe(CONTENT);

});
