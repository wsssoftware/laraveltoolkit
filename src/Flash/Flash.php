<?php

namespace Laraveltoolkit\Flash;

use Illuminate\Support\Collection;
use Inertia\Inertia;

class Flash
{
    use FlashTests;

    protected function add(Message $message): Message
    {
        Inertia::flash($message->id, FlashResource::make($message));

        return $message;
    }

    /**
     * @return Collection<string, Message>
     */
    public function getFlashed(): Collection
    {
        return collect(Inertia::getFlashed())
            ->map(fn (FlashResource $resource) => $resource->resource);
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
