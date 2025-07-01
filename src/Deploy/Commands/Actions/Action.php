<?php

namespace Laraveltoolkit\Deploy\Commands\Actions;

use Closure;
use Illuminate\Console\Command;
use RuntimeException;

use function Laravel\Prompts\spin;

abstract class Action extends Command
{
    protected static bool $fake = false;

    public static function fake(): void
    {
        self::$fake = true;
    }

    protected function getCwdOption(): string
    {
        $cwd = $this->option('cwd') ?? app()->basePath();
        if (! is_dir($cwd)) {
            throw new RuntimeException("The directory $cwd does not exist");
        }

        return $cwd;
    }

    protected function spin(Closure $fn, string $doing, string $success, string $failure): void
    {
        $result = spin($fn, $doing);
        if ($result === 0) {
            $this->components->info($success);
        } else {
            $this->components->error($failure);
        }
    }

    abstract public static function shortLabel(): string;
}
