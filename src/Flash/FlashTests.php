<?php

namespace Laraveltoolkit\Flash;

use PHPUnit\Framework\Assert as PHPUnit;

trait FlashTests
{
    public function assertFlashed(?Severity $severity = null, null|int|string $countOrMessage = null): void
    {
        $messages = $this->messages;
        if ($severity) {
            $messages = $messages->filter(fn(Message $message) => $message->severity === $severity);
        }
        $severity = $severity?->value ?? 'any';
        if (is_string($countOrMessage)) {
            $messages = $messages->filter(fn(Message $message) => $message->detail === $countOrMessage);
        }
        if (is_int($countOrMessage)) {
            PHPUnit::assertCount(
                $countOrMessage, $messages,
                sprintf(
                    'Was expected %s flashes from "%s" severity but was found %s',
                    $countOrMessage,
                    $severity,
                    $messages->count()
                )
            );
        } else {
            $commonStart = sprintf('Was expected a flash of "%s" severity', $severity);
            PHPUnit::assertTrue(
                $messages->isNotEmpty(),
                is_string($countOrMessage)
                    ? sprintf('%s with detail of "%s" but was not found', $commonStart, $countOrMessage)
                    : sprintf('%s but was not found', $commonStart)
            );
        }

    }

    public function assertNotFlashed(?Severity $severity = null): void
    {
        $messages = $this->messages;
        if ($severity) {
            $messages = $messages->filter(fn(Message $message) => $message->severity === $severity);
        }
        $severity = $severity?->value ?? 'any';
        PHPUnit::assertTrue(
            $messages->isEmpty(),
            sprintf('Was expected none flashes of "%s" severity but was found %s', $severity, $messages->count())
        );
    }
}
