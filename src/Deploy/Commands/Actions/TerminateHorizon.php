<?php

namespace Laraveltoolkit\Deploy\Commands\Actions;

class TerminateHorizon extends Action
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deploy:terminate_horizon';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Terminate Horizon';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (self::$fake) {
            return self::SUCCESS;
        }
        $this->spin(
            fn() => $this->callSilent('horizon:terminate'),
            'Terminating Horizon...',
            'Horizon terminated successfully!',
            'Horizon could not be terminated.'
        );

        return self::SUCCESS;
    }

    public static function shortLabel(): string
    {
        return 'Terminate Horizon';
    }
}
