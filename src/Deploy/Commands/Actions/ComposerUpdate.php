<?php

namespace Laraveltoolkit\Deploy\Commands\Actions;

use Symfony\Component\Process\Process;

use function Laravel\Prompts\spin;

class ComposerUpdate extends Action
{
    public $signature = 'composer:update {--c|cwd= : Current work directory for command}';

    public $description = 'Update composer dependencies';

    public function handle(): int
    {
        if (self::$fake) {
            return self::SUCCESS;
        }
        ini_set('memory_limit', '-1');
        $cmd = [
            'composer',
            'update',
        ];

        if (app()->isProduction()) {
            $label = 'Running Composer update in production mode...';
            $cmd[] = '--optimize-autoloader';
            $cmd[] = '--no-dev';
        } else {
            $label = 'Running Composer update in development mode...';
        }

        $process = new Process($cmd, $this->getCwdOption(), env: ['COMPOSER_ALLOW_SUPERUSER' => '1'], timeout: 300);
        $result = spin(fn() => $process->run(), $label);
        if ($result === 0) {
            $this->components->info('Composer updated successfully!');
        } else {
            $this->components->error($process->getErrorOutput());
        }

        return self::SUCCESS;
    }

    public static function shortLabel(): string
    {
        return 'Composer Update';
    }
}
