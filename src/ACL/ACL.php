<?php

namespace Laraveltoolkit\ACL;

use BackedEnum;
use Closure;
use Exception;
use Illuminate\Foundation\Auth\User;
use ReflectionEnum;
use StringBackedEnum;

/**
 * @see \Laraveltoolkit\Facades\ACL
 */
class ACL
{
    protected readonly string $model;

    protected readonly string $rolesEnum;

    public function rolesEnum(): ?string
    {
        return $this->rolesEnum ?? null;
    }

    public function gatePermissions(): ?array
    {
        return $this->permissions(Format::ONLY_VALUES);
    }

    /**
     * @return class-string<\Laraveltoolkit\ACL\UserPermission>
     */
    public function model(): ?string
    {
        return $this->model ?? null;
    }

    public function permissions(Format $format = Format::COMPLETE, ?Closure $filter = null, ?User $user = null): ?array
    {
        $userPermission = $this->userPermission($user);
        if ($userPermission === null) {
            return null;
        }

        return $userPermission->permissions($format, $filter);

    }

    public function userPermission(?User $user = null): ?UserPermission
    {
        return ($user ?? auth()->user())?->userPermission;
    }

    public function withModel(string $model): self
    {
        throw_if(! is_subclass_of($model, UserPermission::class), Exception::class, 'Model must extends UserPermission');
        $this->model = $model;

        return $this;
    }

    /**
     * @param  class-string<StringBackedEnum>  $enum
     * @return $this
     */
    public function withRolesEnum(string $enum): self
    {
        throw_if(! is_subclass_of($enum, BackedEnum::class), Exception::class, 'RoleEnum must be an enum');
        $r = new ReflectionEnum($enum);
        throw_if($r->getBackingType()?->getName() !== 'string', Exception::class,
            'RoleEnum must have a backing type string');
        throw_if(! in_array(HasDenyResponse::class, $r->getInterfaceNames()), Exception::class,
            'RoleEnum must to implement "Laraveltoolkit\ACL\HasDenyResponse"');
        $this->rolesEnum = $enum;

        return $this;
    }
}
