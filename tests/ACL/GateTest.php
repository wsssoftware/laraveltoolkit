<?php

use Laraveltoolkit\Tests\Model\User;
use Laraveltoolkit\Tests\UserRole;

it('test base gate functions', function () {
    $user = User::query()->firstOrFail();

    $user->userPermission->users->create->value = true;
    $user->userPermission->grantRole(UserRole::USER);
    $user->userPermission->saveOrFail();
    $user->userPermission->refresh();
    $this->actingAs($user);

    expect(Gate::allows('users::create'))->toBeTrue()
        ->and(Gate::allows('roles::user'))->toBeTrue()
        ->and(Gate::allows('users::read'))->toBeFalse()
        ->and(Gate::allows('roles::admin'))->toBeFalse()
        ->and(Gate::abilities())
        ->toBeArray()
        ->toHaveCount(15);
});

it('test null on not setted model and enum', function () {
    expect(Gate::abilities())
        ->toBeArray()
        ->toHaveCount(0);
});

it('test middleware', function () {
    Route::get('/test1', fn () => 'ok')
        ->middleware(['web', 'user_roles:user'])
        ->name('test1');
    Route::get('/test2', fn () => 'ok')
        ->middleware(['web', 'user_roles:admin'])
        ->name('test2');

    $user = User::query()->firstOrFail();

    $user->userPermission->grantRole(UserRole::USER);
    $user->userPermission->saveOrFail();
    $user->userPermission->refresh();

    $this->actingAs($user)->get(route('test1'))->assertSuccessful();
    $this->actingAs($user)->get(route('test2'))->assertStatus(403);
});
