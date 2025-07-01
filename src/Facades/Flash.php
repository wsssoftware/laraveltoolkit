<?php

namespace Laraveltoolkit\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Laraveltoolkit\Flash\Message;
use Laraveltoolkit\Flash\Severity;

/**
 * @method static void clear()
 * @method static Collection<int, Message> pullMessages()
 * @method static Message success(string $detail, string $summary = null)
 * @method static Message info(string $detail, string $summary = null)
 * @method static Message warn(string $detail, string $summary = null)
 * @method static Message error(string $detail, string $summary = null)
 * @method static Message secondary(string $detail, string $summary = null)
 * @method static Message contrast(string $detail, string $summary = null)
 * @method static void assertFlashed(?Severity $severity = null, null|int|string $countOrMessage = null)
 * @method static void assertNotFlashed(?Severity $severity = null)
 *
 * @see \Laraveltoolkit\Flash\Flash
 */
class Flash extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Laraveltoolkit\Flash\Flash::class;
    }
}
