<?php

namespace Laraveltoolkit\Facades;

use Closure;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Facade;
use Laraveltoolkit\ACL\Format;
use Laraveltoolkit\ACL\UserPermission;

/**
 * @method static null|string rolesEnum()
 * @method static null|array gatePermissions()
 * @method static null|string model()
 * @method static null|array permissions(Format $format = Format::COMPLETE, ?Closure $filter = null, User $user = null):
 * @method static null|UserPermission userPermission(?User $user = null)
 * @method static \Laraveltoolkit\ACL\ACL withModel(string $model)
 * @method static \Laraveltoolkit\ACL\ACL withRolesEnum(string $enum)
 *
 * @see \Laraveltoolkit\ACL\ACL
 */
class ACL extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Laraveltoolkit\ACL\ACL::class;
    }
}
