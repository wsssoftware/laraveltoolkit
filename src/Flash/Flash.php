<?php

namespace Laraveltoolkit\Flash;

use Illuminate\Support\Collection;

class Flash
{
    use FlashTests;

    protected const string SESSION_KEY = 'lt.flashes';

    protected Collection $messages;

    public function __construct()
    {
        $this->messages = collect();
    }

    protected function add(Message $message): Message
    {
        $this->messages->push($message);
        session()->push(self::SESSION_KEY, $message);

        return $message;
    }

    public function clear(): void
    {
        session()->put(self::SESSION_KEY, []);
        $this->messages = collect();
    }

    public function pullMessages(): Collection
    {
        return collect(session()->pull(self::SESSION_KEY, []))
            ->ensure(Message::class)
            ->sortBy('createdAt');
    }

    public function success(string $detail, ?string $summary = null): Message
    {
        return $this->add(Message::create(Severity::SUCCESS, $detail, $summary));
    }

    public function info(string $detail, ?string $summary = null): Message
    {
        return $this->add(Message::create(Severity::INFO, $detail, $summary));
    }

    public function warn(string $detail, ?string $summary = null): Message
    {
        return $this->add(Message::create(Severity::WARN, $detail, $summary));
    }

    public function error(string $detail, ?string $summary = null): Message
    {
        return $this->add(Message::create(Severity::ERROR, $detail, $summary));
    }

    public function secondary(string $detail, ?string $summary = null): Message
    {
        return $this->add(Message::create(Severity::SECONDARY, $detail, $summary));
    }

    public function contrast(string $detail, ?string $summary = null): Message
    {
        return $this->add(Message::create(Severity::CONTRAST, $detail, $summary));
    }
}
