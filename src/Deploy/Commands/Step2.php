<?php

namespace Laraveltoolkit\Deploy\Commands;

use Illuminate\Support\Facades\Cache;
use Laraveltoolkit\Deploy\Events\MaintenanceDisabledEvent;
use Laraveltoolkit\Deploy\Intent;

class Step2 extends Step
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deploy:2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will deploy the application';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->components->alert('Deploying application Step 2');
        $this->intents(2)
            ->each(fn (Intent $intent) => $intent->call($this));

        $this->call('up');
        Cache::forget('deploy::must_offer_step_two_as_default');
        MaintenanceDisabledEvent::dispatch();

        $this->components->info('Step 2 finished, your deploy was done!');

        return self::SUCCESS;
    }
}
