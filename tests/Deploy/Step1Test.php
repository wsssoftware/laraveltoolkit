<?php

use Laraveltoolkit\Deploy\Commands\Actions\ComposerUpdate;
use Laraveltoolkit\Deploy\Commands\Actions\GitPull;

it('can run step1', function () {
    GitPull::fake();
    ComposerUpdate::fake();
    $this->artisan('deploy')
        ->expectsQuestion('Witch step of deploy do you want to run?', 1)
        ->expectsOutputToContain('Step 1 finished, php deploy gain and choose step 2');

    $this->artisan('up');
});
