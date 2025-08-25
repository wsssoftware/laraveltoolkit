<?php

namespace Laraveltoolkit\Support;

trait HasTimeoutHandler
{
    private readonly float $timeoutHandlerStartedAt;

    protected int $safeTimeout = 55;

    protected function startTimeoutHandler(): void
    {
        $this->timeoutHandlerStartedAt = microtime(true);
    }

    /**
     * @throws \Throwable
     */
    protected function elapsedTime(): float
    {
        throw_if(empty($this->timeoutHandlerStartedAt), 'Timeout handler not started');

        return microtime(true) - $this->timeoutHandlerStartedAt;

    }

    /**
     * @throws \Throwable
     */
    protected function itsApproachingTimeout(): bool
    {
        return $this->elapsedTime() > $this->safeTimeout;
    }
}
