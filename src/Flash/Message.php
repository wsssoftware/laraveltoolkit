<?php

namespace Laraveltoolkit\Flash;

use Illuminate\Support\Str;

class Message
{
    private function __construct(
        readonly public string $id,
        readonly public Severity $severity,
        readonly public ?string $summary,
        readonly public string $detail,
        public ?bool $closable,
        public ?int $life,
        public ?string $group,
        readonly public float $createdAt
    ) {}

    public static function create(
        Severity $severity,
        string $detail,
        ?string $summary = null,
    ): Message {
        return new Message(
            Str::uuid()->toString(),
            $severity,
            $summary,
            $detail,
            config('laraveltoolkit.flash.defaults.closable'),
            config('laraveltoolkit.flash.defaults.life'),
            config('laraveltoolkit.flash.defaults.group'),
            microtime(true)
        );
    }

    public function closable(): self
    {
        $this->closable = true;

        return $this;
    }

    public function unclosable(): self
    {
        $this->closable = false;

        return $this;
    }

    public function withGroup(string $group): self
    {
        $this->group = $group;

        return $this;
    }

    public function withLife(int $life): self
    {
        $this->life = $life;

        return $this;
    }
}
