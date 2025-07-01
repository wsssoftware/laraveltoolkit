<?php

namespace Laraveltoolkit\Tests;

use Illuminate\Support\ServiceProvider;
use Laraveltoolkit\Facades\ACL;
use Laraveltoolkit\Tests\Model\UserPermission;

class TestServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ACL::withModel(UserPermission::class)
            ->withRolesEnum(UserRole::class);
    }
}
