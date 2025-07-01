<?php

namespace Laraveltoolkit\Deploy\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Laraveltoolkit\Deploy\Intent;

use function Laravel\Prompts\select;

class Run extends Command
{
    use HasIntents;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'deploy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run deploy command.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $selected = select(
            'Witch step of deploy do you want to run?',
            [1 => $this->getLabel(1), 2 => $this->getLabel(2)],
            Cache::has('deploy::must_offer_step_two_as_default') ? 2 : 1
        );
        if ($selected === 1) {
            $this->call('deploy:1');
        } elseif ($selected === 2) {
            $this->call('deploy:2');
        }

        return self::SUCCESS;
    }

    protected function getLabel(int $step): string
    {
        $items = $this->intents($step)->map(fn(Intent $item) => $item->shortLabel());

        return sprintf(
            'Step %d: (%s)',
            $step,
            $items->isNotEmpty()
                ? Arr::join($items->toArray(), ', ', ' and ')
                : 'NO ACTIONS DEFINED'
        );
    }
}
