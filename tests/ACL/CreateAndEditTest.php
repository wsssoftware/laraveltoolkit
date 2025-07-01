<?php

use Illuminate\Support\Collection;
use Laraveltoolkit\ACL\Rule;
use Laraveltoolkit\Tests\Model\User;
use Laraveltoolkit\Tests\Model\UserPermission;
use Laraveltoolkit\Tests\UserRole;

it('create user permission on user create', function () {
    expect(User::query()->where('id', 100)->exists())
        ->toBeFalse()
        ->and(UserPermission::query()->where('id', 100)->exists())
        ->toBeFalse();

    (new User(['id' => 100, 'name' => 'test', 'email' => 'test@test.com', 'password' => 'abc']))->saveOrFail();

    $user = User::query()->where('id', 100)->firstOrFail();

    expect(User::query()->where('id', 100)->exists())
        ->toBeTrue()
        ->and(UserPermission::query()->where('id', 100)->exists())
        ->toBeFalse()
        ->and($user->userPermission)
        ->toBeInstanceOf(UserPermission::class)
        ->and(UserPermission::query()->where('id', 100)->exists())
        ->toBeTrue();

    foreach ($user->userPermission->getPolicies() as $policy) {
        foreach ($policy->rules as $rule) {
            expect($rule)
                ->toBeInstanceOf(Rule::class)
                ->and($rule->value)
                ->toBeFalse();
        }
    }
    expect($user->userPermission->roles)
        ->toBeInstanceOf(Collection::class)
        ->toBeEmpty();
});

it('has the correct behaviour on update values', function () {

    UserPermission::query()->where('id', 1)->delete();
    $user = User::query()->where('id', 1)->firstOrFail();

    foreach ($user->userPermission->getPolicies() as $policy) {
        foreach ($policy->rules as $rule) {
            expect($rule)
                ->toBeInstanceOf(Rule::class)
                ->and($rule->value)
                ->toBeFalse();
        }
    }
    $user->userPermission->users->create->value = true;
    $user->userPermission->fillPolicies(['products::create' => true]);
    $user->userPermission->categories = ['create' => true];
    $user->userPermission->roles = collect([UserRole::USER]);

    $user->userPermission->saveOrFail();

    expect($user->userPermission->roles->count())
        ->toBe(1)
        ->and($user->userPermission->roles->first())
        ->toEqual(UserRole::USER);

    foreach ($user->userPermission->getPolicies() as $policy) {
        foreach ($policy->rules as $rule) {
            expect($rule)
                ->toBeInstanceOf(Rule::class)
                ->and($rule->value)
                ->toEqual($rule->key === 'create');
        }
    }
});

it('test if changes directly value from rule save', function () {
    UserPermission::query()->where('id', 1)->delete();
    $user = User::query()->where('id', 1)->firstOrFail();
    $user->userPermission->users->create->value = true;
    expect($user->userPermission->users->create->isDirty())->toBeTrue();
    $user->userPermission->saveOrFail();
});

it('has the correct behaviour on grant and denies', function () {
    $user = User::query()->where('id', 1)->firstOrFail();

    foreach ($user->userPermission->getPolicies() as $policy) {
        foreach ($policy->rules as $rule) {
            expect($rule->value)
                ->toBeFalse();
        }
    }
    $user->userPermission->grant('users::create');
    foreach ($user->userPermission->users->rules as $rule) {
        expect($rule->value)
            ->toBe($rule->key === 'create');
    }
    $user->userPermission->grantAll('users');
    foreach ($user->userPermission->users->rules as $rule) {
        expect($rule->value)
            ->toBeTrue();
    }
    $user->userPermission->deny('users::create');
    foreach ($user->userPermission->users->rules as $rule) {
        expect($rule->value)
            ->toBe($rule->key !== 'create');
    }

    $user->userPermission->denyAll('users');
    foreach ($user->userPermission->users->rules as $rule) {
        expect($rule->value)
            ->toBeFalse();
    }

    $user->userPermission->grantAll();
    foreach ($user->userPermission->getPolicies() as $policy) {
        foreach ($policy->rules as $rule) {
            expect($rule->value)
                ->toBeTrue();
        }
    }

    $user->userPermission->denyAll();
    foreach ($user->userPermission->getPolicies() as $policy) {
        foreach ($policy->rules as $rule) {
            expect($rule->value)
                ->toBeFalse();
        }
    }

    $user->userPermission->denyAllRoles();
    expect($user->userPermission->roles)
        ->toBeInstanceOf(Collection::class)
        ->toBeEmpty();

    $user->userPermission->grantAllRoles();
    expect($user->userPermission->roles)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(2)
        ->unique()
        ->each
        ->toBeInstanceOf(UserRole::class);

    $user->userPermission->denyRole(UserRole::ADMIN);
    expect($user->userPermission->roles)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(1)
        ->unique()
        ->each
        ->toBeInstanceOf(UserRole::class)
        ->toEqual(UserRole::USER);

    $user->userPermission->denyAllRoles();
    $user->userPermission->grantRole(UserRole::ADMIN);
    expect($user->userPermission->roles)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(1)
        ->unique()
        ->each
        ->toBeInstanceOf(UserRole::class)
        ->toEqual(UserRole::ADMIN);
});
