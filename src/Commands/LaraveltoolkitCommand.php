<?php

namespace Laraveltoolkit\Commands;

use Illuminate\Console\Command;

class LaraveltoolkitCommand extends Command
{
    public $signature = 'laraveltoolkit';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
