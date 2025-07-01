<?php

use Laraveltoolkit\Deploy\MaintenanceController;
use Laraveltoolkit\Deploy\PreventRequestsDuringMaintenance;

it('has correct value in middleware', function () {
    $middleware = new PreventRequestsDuringMaintenance(app());

    $reflector = new ReflectionClass($middleware);
    $property = $reflector->getProperty('except');

    expect($property->getValue($middleware))
        ->toBeArray()
        ->toContain(config('laraveltoolkit.deploy.path'));
});

it('redirect if not on maintenance', function () {
    app()->maintenanceMode()->deactivate();
    $response = $this->get(config('laraveltoolkit.deploy.path'));

    $response->assertRedirect(config('laraveltoolkit.deploy.default_redirect'));
});

it('can render maintenance', function () {
    app()->maintenanceMode()->activate(['redirect' => '/maintenance']);

    $controller = new MaintenanceController;

    expect($controller(request()))
        ->toBeInstanceOf(\Inertia\Response::class);
});
