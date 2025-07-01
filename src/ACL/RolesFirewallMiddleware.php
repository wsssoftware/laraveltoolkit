<?php

namespace Laraveltoolkit\ACL;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Stringable;
use Laraveltoolkit\Facades\ACL;
use Symfony\Component\HttpFoundation\Response;

class RolesFirewallMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        /** @var \BackedEnum&\Laraveltoolkit\ACL\HasDenyResponse|null $enum */
        $enum = ACL::rolesEnum();
        $roleGroups = collect($roles)
            ->map(fn (string $role) => str($role))
            ->groupBy(fn (Stringable $role) => $role->startsWith('!') ? 'denies' : 'allows')
            ->mapWithKeys(fn (Collection $group, string $index) => [
                $index => $group->map(fn (Stringable $role) => $enum::from($role->after('!')->toString())),
            ]);
        foreach ($roleGroups as $method => $roles) {
            /** @var \BackedEnum&\Laraveltoolkit\ACL\HasDenyResponse $role */
            foreach ($roles as $role) {
                $ability = "roles::$role->value";
                if (! Gate::{$method}($ability)) {
                    $role->denyResponse()->authorize();
                }
            }
        }

        return $next($request);
    }
}
