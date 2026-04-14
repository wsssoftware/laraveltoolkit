<?php

namespace Laraveltoolkit\ACL;

use Closure;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Laraveltoolkit\Facades\ACL;
use StringBackedEnum;

/**
 * @property int $id
 * @property Collection<string, \UnitEnum> $roles
 * @property Carbon $updated_at
 *
 * @method Policy __get(string $name)
 */
abstract class UserPermission extends Model
{
    use ManagementTrait;

    public const CREATED_AT = null;

    /**
     * @var Collection<string, Policy>
     */
    protected Collection $policies;

    protected StringBackedEnum $enum;

    public function __construct(array $attributes = [])
    {
        $this->startup();
        parent::__construct($attributes);
    }

    public static function boot(): void
    {
        self::saving(function (UserPermission $model) {
            if ($model->isDirty('roles')) {
                $model->roles = $model->roles->unique()->values();
            }
        });
        parent::boot();
    }

    /**
     * This method declare policies for your ACL system
     */
    abstract protected function declarePoliciesAndRoles(): void;

    /**
     * @return Collection<string, Policy>
     */
    public function getPolicies(?Closure $filter = null): Collection
    {
        $policies = collect();
        foreach ($this->policies as $policy) {
            $policies->put($policy->column, $this->{$policy->column});
        }
        if ($filter) {
            $policies = $policies->filter($filter);
        }

        return ! empty($column) ? $policies->get($column) : $policies;
    }

    final public function casts(): array
    {
        $enum = ACL::rolesEnum();
        $cast = [
            'id' => 'int',
            'roles' => $enum ? AsEnumCollection::of($enum) : 'collection',
        ];
        foreach ($this->policies as $policy) {
            $cast[$policy->column] = PolicyCast::class;
        }
        $cast['updated_at'] = 'datetime';

        return $cast;
    }

    public function permissions(Format $format = Format::COMPLETE, ?Closure $filter = null): array
    {
        return match ($format) {
            Format::COMPLETE => CompleteResource::make($this->getPolicies($filter))->resolve(),
            Format::ONLY_VALUES => OnlyValueResource::make($this->getPolicies($filter))->resolve(),
        };
    }
}
