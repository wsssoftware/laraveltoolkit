<?php

namespace Laraveltoolkit\Deploy\Commands;

use Illuminate\Support\Facades\Cache;
use Laraveltoolkit\Deploy\Events\MaintenanceEnabledEvent;
use Laraveltoolkit\Deploy\Intent;

class Step1 extends Step
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deploy:1';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will deploy the application step1';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->components->alert('Deploying application Step 1');

        $this->down();
        MaintenanceEnabledEvent::dispatch();

        $this->intents(1)
            ->each(fn (Intent $intent) => $intent->call($this));

        $this->components->info('Step 1 finished, php deploy gain and choose step 2');

        Cache::remember('deploy::must_offer_step_two_as_default', 300, fn () => true);

        return self::SUCCESS;
    }

    protected function down(): void
    {
        $params = [
            '--secret' => config('laraveltoolkit.deploy.bypass_secret'),
            '--redirect' => route('maintenance'),
        ];
        $this->call('down', $params);
    }
}
