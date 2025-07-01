<?php

namespace Laraveltoolkit\Support\Document;

use Exception;
use Illuminate\Support\Lottery;
use Laraveltoolkit\Facades\Regex;

class Generic implements Document
{
    public function checkDigits(string $document): string
    {
        $strlen = strlen(Regex::onlyNumeric($document));
        throw_if(!in_array($strlen, [9, 11, 12, 14]), Exception::class, 'Invalid document');

        return match ($strlen) {
            9, 11 => app(CPF::class)->checkDigits($document),
            12, 14 => app(CNPJ::class)->checkDigits($document),
        };
    }

    public function fake(): string
    {
        return Lottery::odds(0.5)
            ->winner(fn() => app(CPF::class)->fake())
            ->loser(fn() => app(CNPJ::class)->fake())
            ->choose();
    }

    public function label(): string
    {
        return 'Genérico';
    }

    public function mask(string $document): string
    {
        $strlen = strlen(Regex::onlyNumeric($document));
        throw_if(!in_array($strlen, [11, 14]), Exception::class, 'Invalid document');

        return match ($strlen) {
            11 => app(CPF::class)->mask($document),
            14 => app(CNPJ::class)->mask($document),
        };
    }

    public function unmask(string $document): string
    {
        return Regex::onlyNumeric($document);
    }

    public function validate(string $document): bool
    {
        $strlen = strlen(Regex::onlyNumeric($document));

        return match ($strlen) {
            11 => app(CPF::class)->validate($document),
            14 => app(CNPJ::class)->validate($document),
            default => false,
        };
    }
}
