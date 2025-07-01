<?php

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laraveltoolkit\Inertia\HandleInertiaCrossDomainVisits;

const CONTENT = 'used next';

$successClosure = fn () => new \Symfony\Component\HttpFoundation\Response(CONTENT);

it('has normal response when without Inertia header', function () use ($successClosure) {
    $url = 'http://localhost';
    $request = Request::create($url);

    /** @var \Laraveltoolkit\Inertia\HandleInertiaCrossDomainVisits $middleware */
    $middleware = app(HandleInertiaCrossDomainVisits::class);

    $response = $middleware->handle($request, $successClosure);
    expect($response->getContent())
        ->toBe(CONTENT);

});

it('has normal response when not GET method', function () use ($successClosure) {
    $url = 'http://localhost';

    $request = Request::create($url, 'POST');
    $request->headers->set('X-Inertia', true);

    /** @var \Laraveltoolkit\Inertia\HandleInertiaCrossDomainVisits $middleware */
    $middleware = app(HandleInertiaCrossDomainVisits::class);

    $response = $middleware->handle($request, $successClosure);
    expect($response->getContent())
        ->toBe(CONTENT);

});

it('has normal response when host equal', function () use ($successClosure) {
    $url = 'http://localhost';

    $request = Request::create($url);
    $request->headers->set('X-Inertia', true);

    /** @var \Laraveltoolkit\Inertia\HandleInertiaCrossDomainVisits $middleware */
    $middleware = app(HandleInertiaCrossDomainVisits::class);

    $response = $middleware->handle($request, $successClosure);
    expect($response->getContent())
        ->toBe(CONTENT);

});

it('has location response due different domain', function () use ($successClosure) {
    $url = 'http://b.localhost';

    $request = Request::create($url);
    $request->headers->set('X-Inertia', true);

    /** @var \Laraveltoolkit\Inertia\HandleInertiaCrossDomainVisits $middleware */
    $middleware = app(HandleInertiaCrossDomainVisits::class);

    $response = $middleware->handle($request, $successClosure);
    expect($response)
        ->toBeInstanceOf(RedirectResponse::class)
        ->and($response->headers->has('Location'))
        ->toBeTrue();

});
