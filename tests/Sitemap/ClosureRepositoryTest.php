<?php

use Laraveltoolkit\Sitemap\ClosureRepository;
use Orchestra\Testbench\Factories\UserFactory;
use Workbench\App\Models\User;

it('can instantiate collection', function () {
    $cr = new ClosureRepository(collect([1, 2, 3]), function (int $int) {
        expect(in_array($int, [1, 2, 3]))->toBe(true);
    });
    $cr->resolve();
});

it('can instantiate model', function () {
    $factory = app(UserFactory::class);
    $factory->count(10)->create();
    $cr = new ClosureRepository(User::query(), function ($user) {
        expect($user)->toBeInstanceOf(User::class);
    });
    $cr->resolve();
});

it('can instantiate model with limit', function () {
    $factory = app(UserFactory::class);
    $factory->count(10)->create();
    $cr = new ClosureRepository(User::query()->limit(5), function ($user) {
        expect($user)->toBeInstanceOf(User::class);
    });

    $cr->resolve();
});
