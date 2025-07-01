<?php

use Laraveltoolkit\ACL\Format;
use Laraveltoolkit\ACL\Policy;
use Laraveltoolkit\Facades\ACL;
use Laraveltoolkit\Tests\Model\User;

it('can get permission', function () {
    $result = ACL::permissions();
    expect($result)->toBeNull();

    $this->actingAs(User::query()->first());
    $result = ACL::permissions();
    expect($result)->toBeArray()
        ->toHaveCount(13)
        ->each
        ->toHaveKeys([
            'id',
            'policy_column',
            'policy_name',
            'policy_description',
            'rule_key',
            'rule_name',
            'rule_description',
            'rule_deny_status',
            'rule_value',
        ]);

    $result = ACL::permissions(Format::ONLY_VALUES);
    expect($result)->toBeArray()
        ->toHaveCount(13)
        ->each
        ->toBeBool();

    $result = ACL::permissions(Format::ONLY_VALUES, function (Policy $policy) {
        return $policy->column === 'users';
    });

    expect($result)->toBeArray()
        ->toHaveCount(5)
        ->each
        ->toBeBool();
});

it('can get gate permission', function () {
    $this->actingAs(User::query()->first());
    $result = ACL::gatePermissions();
    expect($result)->toBeArray()
        ->toHaveCount(13)
        ->each
        ->toBeBool();
});
