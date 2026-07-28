<?php

use Laraveltoolkit\Support\HasTimeoutHandler;

it('has a safe default timeout', function () {
    $handler = new class
    {
        use HasTimeoutHandler;

        public function safeTimeoutInSeconds(): int
        {
            return $this->safeTimeout();
        }
    };

    expect($handler->safeTimeoutInSeconds())->toBe(55);
});
