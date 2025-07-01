<?php

namespace Laraveltoolkit\ACL;

use Illuminate\Console\GeneratorCommand;

class MakeACLModelCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:acl-model';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the ACL model';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'ACL Model';

    protected function getStub(): string
    {
        $relativePath = '/stubs/recipe.stub';

        $customPath = $this->laravel->basePath(trim($relativePath, '/'));

        return file_exists($customPath) ? $customPath : __DIR__.$relativePath;
    }

    protected function getNameInput(): string
    {
        return 'UserPermission';
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Models';
    }
}
