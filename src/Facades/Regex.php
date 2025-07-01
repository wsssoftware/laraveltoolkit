<?php

namespace Laraveltoolkit\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array getHashtags(string $payload)
 * @method static bool isEmail(string $payload)
 * @method static bool isHexColor(string $payload)
 * @method static bool isIPv4Address(string $payload)
 * @method static bool isIPv6Address(string $payload)
 * @method static bool isIPAddress(string $payload)
 * @method static bool isLikePhpVariableChars(string $payload)
 * @method static bool isSequenceOfUniqueChar(string $payload)
 * @method static bool isURL(string $payload)
 * @method static string onlyAlpha(?string $payload, bool $allowSpace = false, bool $allowAccents = true)
 * @method static string onlyAlphaNumeric(?string $payload, bool $allowSpace = false, bool $allowAccents = true)
 * @method static string onlyNumeric(?string $payload, bool $allowSpace = false)
 *
 * @see \Laraveltoolkit\Support\Regex
 */
class Regex extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Laraveltoolkit\Support\Regex::class;
    }
}
