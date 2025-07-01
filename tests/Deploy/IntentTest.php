<?php

use Laraveltoolkit\Deploy\Commands\Actions\CacheApplication;
use Laraveltoolkit\Deploy\Commands\Actions\GitPull;
use Laraveltoolkit\Deploy\Intent;
use Laraveltoolkit\Tests\Deploy\Command;
use Mockery\MockInterface;

it('test intent methods', function () {
    $intent = new Intent(CacheApplication::class, []);
    $command = $this->mock(Command::class, function (MockInterface $mock) {
        $mock->shouldReceive('call')->once();
    });
    expect(Intent::make(GitPull::class, ['foo']))
        ->toBeArray()
        ->toHaveKeys([0, 1])
        ->and($intent->shortLabel())
        ->toBeString()
        ->and($intent->call($command))
        ->toEqual(0);
});
