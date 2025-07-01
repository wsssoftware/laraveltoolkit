<?php

namespace Laraveltoolkit\StoredAssets;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeStoreRecipeCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:store-recipe
                            {name : Name of the recipe}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Store Recipe';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Store recipe';

    protected function getStub(): string
    {
        $relativePath = '/stubs/recipe.stub';

        $customPath = $this->laravel->basePath(trim($relativePath, '/'));

        return file_exists($customPath) ? $customPath : __DIR__.$relativePath;
    }

    protected function getNameInput(): string
    {
        $name = parent::getNameInput();
        if (Str::endsWith($name, ['Recipe', 'recipe'])) {
            $name = Str::substr($name, 0, -6);
        }

        return $name.'Recipe';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\StoreRecipes';
    }
}
