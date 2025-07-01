<?php

namespace Laraveltoolkit\ACL;

use BackedEnum;
use Exception;
use Laraveltoolkit\Facades\ACL;

trait ManagementTrait
{
    private function startup(): void
    {
        $this->policies = collect();
        $this->declarePoliciesAndRoles();
        $this->policies = $this->policies
            ->map(fn(Policy|PolicyMaker $p) => $p instanceof PolicyMaker ? $p->toPolicy() : $p);
        $this->fillable[] = 'id';
        $this->fillable[] = 'roles';
        foreach ($this->policies as $policy) {
            $this->fillable[] = $policy->column;
        }
        $this->fillable[] = 'updated_at';
    }

    protected function registryPolicy(string $column, string $name, string $description): PolicyMaker
    {
        $this->policies->put($column, new PolicyMaker(collect(), $column, $name, $description));

        return $this->policies->get($column);
    }

    public function fillPolicies(array $policies): void
    {
        foreach ($policies as $policy => $value) {
            [$policy, $rule] = explode('::', $policy);
            $this->{$policy}->{$rule}->value = $value;
        }
    }

    public function denyAll(?string $policy = null): self
    {
        $policies = $policy !== null ? [$this->policies->get($policy)] : $this->policies;
        foreach ($policies as $policy) {
            foreach ($policy->rules as $rule) {
                $this->{$policy->column}->{$rule->key}->value = false;
            }
        }

        return $this;
    }

    public function deny(string $ability): self
    {
        [$policy, $rule] = explode('::', $ability);
        $this->{$policy}->{$rule}->value = false;

        return $this;
    }

    public function grantAll(?string $policy = null): self
    {
        $policies = $policy !== null ? [$this->policies->get($policy)] : $this->policies;
        foreach ($policies as $policy) {
            foreach ($policy->rules as $rule) {
                $this->{$policy->column}->{$rule->key}->value = true;
            }
        }

        return $this;
    }

    public function grant(string $ability): self
    {
        [$policy, $rule] = explode('::', $ability);
        $this->{$policy}->{$rule}->value = true;

        return $this;
    }

    public function grantRole(BackedEnum $role): self
    {
        $enumType = ACL::rolesEnum();
        throw_if($enumType === null, Exception::class, 'You must configure RoleEnum before use it');
        throw_if(!$role instanceof $enumType, Exception::class, 'Enum must to be instance of '.$enumType);
        $collection = $this->roles;
        $collection->put($role->value, $role);
        $this->roles = $collection;

        return $this;
    }

    public function grantAllRoles(): self
    {
        /** @var BackedEnum|null $enum */
        if ($enum = ACL::rolesEnum()) {
            $collection = collect();
            foreach ($enum::cases() as $case) {
                $collection->put($case->value, $case);
            }
            $this->roles = $collection;
        }

        return $this;
    }

    public function denyRole(BackedEnum $role): self
    {
        $this->roles = $this->roles->filter(fn(BackedEnum $r) => $r->value !== $role->value);

        return $this;
    }

    public function denyAllRoles(): self
    {
        $this->roles = collect();

        return $this;
    }
}
