<?php

namespace Laraveltoolkit\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Inertia\ServiceProvider;
use Laraveltoolkit\LaraveltoolkitServiceProvider;
use Laraveltoolkit\Tests\Model\User;
use Orchestra\Testbench\Attributes\WithMigration;
use Orchestra\Testbench\TestCase as Orchestra;

#[WithMigration]
class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->maintenanceMode()->deactivate();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Laraveltoolkit\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
        $copyPath = dirname(__DIR__).'/routes/sitemap.php';
        $sitemapRoutesPath = base_path('routes/sitemap.php');
        if (! file_exists($sitemapRoutesPath)) {
            copy($copyPath, $sitemapRoutesPath);
        }
        new User(['id' => 1, 'name' => 'Foo Bar', 'email' => 'foo@bar.com', 'password' => 'abc'])->saveOrFail();
    }

    protected function getPackageProviders($app): array
    {
        $providers = [];
        if ($this->name() !== '__pest_evaluable_it_test_null_on_not_setted_model_and_enum') {
            $providers[] = TestServiceProvider::class;
        }

        return array_merge($providers, [
            LaraveltoolkitServiceProvider::class,
            ServiceProvider::class,
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(dirname(__DIR__).'/workbench/database/migrations');
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');
        config()->set('app.locale', 'pt_BR');
        config()->set('app.key', 'base64:Z1sxfk3d54CWnssAxvEFshoZVGmAO7KrbZGMzU5xko4=');

        $migrationStoredAsset = include __DIR__.'/../database/migrations/create_stored_assets_table.php.stub';
        $migrationStoredAsset->up();
        $migrationProduct = include __DIR__.'/Model/2024_09_24_163917_create_products_table.php';
        $migrationProduct->up();
        $migrationUserPermission = include __DIR__.'/Model/create_user_permissions_table.php';
        $migrationUserPermission->up();
    }
}
