<?php

namespace Laraveltoolkit\Deploy\Commands;

use Illuminate\Support\Collection;
use Laraveltoolkit\Deploy\Intent;

trait HasIntents
{
    public function intents(int $step): Collection
    {
        return collect(config("laraveltoolkit.deploy.step$step", []))
            ->map(fn (array $item) => new Intent($item[0], $item[1]));
    }
}
