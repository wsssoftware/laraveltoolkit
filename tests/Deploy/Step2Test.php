<?php

use Laraveltoolkit\Deploy\Commands\Actions\ComposerUpdate;
use Laraveltoolkit\Deploy\Commands\Actions\GitPull;

it('can run step2', function () {
    GitPull::fake();
    ComposerUpdate::fake();
    $this->artisan('deploy')
        ->expectsQuestion('Witch step of deploy do you want to run?', 2)
        ->expectsOutputToContain('Step 2 finished, your deploy was done!');

    $this->artisan('up');
});
