<?php

namespace Laraveltoolkit\Deploy;

use Illuminate\Console\Command;

/**
 * @property class-string<\Laraveltoolkit\Deploy\Commands\Actions\Action> $commandFqn
 */
readonly class Intent
{
    public function __construct(
        public string $commandFqn,
        public array $arguments = [],
    ) {}

    public static function make(string $commandFqn, array $arguments = []): array
    {
        return [$commandFqn, $arguments];
    }

    public function call(Command $command): int
    {
        return $command->call($this->commandFqn, $this->arguments);
    }

    public function shortLabel(): string
    {
        return $this->commandFqn::shortLabel();
    }
}
