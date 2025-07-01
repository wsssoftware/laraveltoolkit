<?php

namespace Laraveltoolkit\Support;

/**
 * @see \Laraveltoolkit\Facades\Regex
 */
class Regex
{
    public function getHashtags(string $payload): array
    {
        $matches = [];
        preg_match_all('/#\w+/', $payload, $matches);

        return $matches[0];
    }

    public function isEmail(string $payload, bool $uncommon = false): bool
    {
        return preg_match('/^((?!\.)[\w\-_.]*[^.])(@\w+)(\.\w+(\.\w+)?[^.\W])$/', $payload) === 1;
    }

    public function isHexColor(string $payload): bool
    {
        return preg_match('/^#([a-fA-F0-9]{6}|[a-fA-F0-9]{3})$/', $payload) === 1;
    }

    public function isIPv4Address(string $payload): bool
    {
        return preg_match(
                '/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/',
                $payload
            ) === 1;
    }

    public function isIPv6Address(string $payload): bool
    {
        return preg_match(
                '/^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))\s*$/',
                $payload
            ) === 1;
    }

    public function isIPAddress(string $payload): bool
    {
        return $this->isIPv4Address($payload) || $this->isIPv6Address($payload);
    }

    public function isLikePhpVariableChars(string $payload): bool
    {
        return preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $payload) === 1;
    }

    public function isSequenceOfUniqueChar(string $payload): bool
    {
        return preg_match('/^(.)\1*$/', $payload) === 1;
    }

    public function isURL(string $payload): bool
    {
        return preg_match(
                '/^(https?:\/\/|ftp:\/\/)(www\.)?([a-zA-Z0-9-]+(\.[a-zA-Z]{2,})*)(:[0-9]{1,5})?(\/[^\s]*)?$/',
                $payload
            ) === 1;
    }

    public function onlyAlpha(?string $payload, bool $allowSpace = false, bool $allowAccents = true): string
    {
        $regex = 'a-zA-Z';
        if ($allowAccents) {
            $regex .= 'À-ÿ';
        }
        if ($allowSpace) {
            $regex .= ' ';
        }

        return preg_replace("/[^$regex]/", '', $payload ?? '');
    }

    public function onlyAlphaNumeric(?string $payload, bool $allowSpace = false, bool $allowAccents = true): string
    {
        $regex = 'a-zA-Z';
        if ($allowAccents) {
            $regex .= 'À-ÿ';
        }
        $regex .= '0-9';
        if ($allowSpace) {
            $regex .= ' ';
        }

        return preg_replace("/[^$regex]/", '', $payload ?? '');
    }

    public function onlyNumeric(?string $payload, bool $allowSpace = false): string
    {
        return match (true) {
            $allowSpace => preg_replace('/[^0-9 ]/', '', $payload ?? ''),
            default => preg_replace('/[^0-9]/', '', $payload ?? ''),
        };
    }
}
