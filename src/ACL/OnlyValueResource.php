<?php

namespace Laraveltoolkit\ACL;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Collection<string, \Laraveltoolkit\ACL\Policy> $resource
 */
class OnlyValueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->resource->reduce(fn(array $c, Policy $p) => $c + $this->reducePolicy($p), []);
    }

    protected function reducePolicy(Policy $policy): array
    {
        return $policy->rules->reduce(fn(array $c, Rule $r) => $c + $this->reduceRule($policy, $r), []);
    }

    protected function reduceRule(Policy $policy, Rule $rule): array
    {
        return ["$policy->column::$rule->key" => $policy->{$rule->key}->value];
    }
}
