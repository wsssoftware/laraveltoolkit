<?php

namespace Laraveltoolkit\Deploy\Commands\Actions;

use Symfony\Component\Process\Process;

use function Laravel\Prompts\spin;

class Pm2Restart extends Action
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pm2:restart {--c|cwd= : Current work directory for command}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restart PM2 processes.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (self::$fake) {
            return self::SUCCESS;
        }
        $process = new Process(['pm2', 'status']);
        $result = spin(fn() => $process->run(), 'Checking PM2 status...');
        if ($result !== 0) {
            $this->components->warn('PM2 is not installed.');

            return self::SUCCESS;
        }

        $process = new Process(['pm2', 'restart', 'all', '--update-env'], $this->getCwdOption(), timeout: 300);
        $result = spin(fn() => $process->run(), 'Restarting PM2 processes');
        if ($result === 0) {
            $this->components->info('PM2 restarted successfully!');
        } else {
            $this->components->error($process->getErrorOutput());
        }

        return self::SUCCESS;
    }

    public static function shortLabel(): string
    {
        return 'PM2 Restart';
    }
}
