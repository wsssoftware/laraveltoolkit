<?php

use Laraveltoolkit\ACL\Policy;
use Laraveltoolkit\ACL\PolicyMaker;
use Laraveltoolkit\ACL\Rule;

it('PolicyMaker works properly', function () {
    $pm = new PolicyMaker(collect(), 'users', 'Users', 'Manage users');

    expect($pm)
        ->toBeInstanceOf(PolicyMaker::class)
        ->and($pm->column)
        ->toBe('users')
        ->and($pm->name)
        ->toBe('Users')
        ->and($pm->description)
        ->toBe('Manage users')
        ->and(fn() => $pm->rule('column', 'column', 'column'))
        ->toThrow('column is a reserved name and cannot used on key.')
        ->and($policy = $pm->crud()->rule('ab', 'ab', 'ab')->toPolicy())
        ->toBeInstanceOf(Policy::class)
        ->and($policy->rules)
        ->toHaveCount(5)
        ->each
        ->toBeInstanceOf(Rule::class)
        ->and($policy->column)
        ->toBe('users')
        ->and($policy->name)
        ->toBe('Users')
        ->and($policy->description)
        ->toBe('Manage users')
        ->and($policy->create)
        ->toBeInstanceOf(Rule::class)
        ->and(fn() => $policy->foo)
        ->toThrow('Property foo does not exist.')
        ->and(fn() => $policy->create->foo)
        ->toThrow('Property foo does not exist.');
});

it('PolicyMaker has default rules working', function () {
    $pm = (new PolicyMaker(collect(), 'users', 'Users', 'Manage users'))
        ->cancel()
        ->create()
        ->delete()
        ->download()
        ->execute()
        ->export()
        ->import()
        ->print()
        ->read()
        ->share()
        ->update()
        ->upload();

    expect($pm)
        ->toBeInstanceOf(PolicyMaker::class)
        ->and($pm->rules->count())
        ->toBe(12);
});
