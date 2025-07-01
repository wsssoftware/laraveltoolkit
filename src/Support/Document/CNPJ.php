<?php

namespace Laraveltoolkit\Support\Document;

use Exception;
use Illuminate\Support\Str;
use Laraveltoolkit\Facades\Regex;

class CNPJ implements Document
{
    public function checkDigits(string $document): string
    {
        $document = Regex::onlyNumeric($document);
        if (strlen($document) > 12) {
            $document = substr($document, 0, 12);
        } elseif (strlen($document) < 12) {
            $document = str_pad($document, 12, '0');
        }
        $c = str($document)->substr(0, 12)->reverse()->toString();

        $closure = function (string $x) {
            return $x[0] * 2 + $x[1] * 3 + $x[2] * 4 + $x[3] * 5 + $x[4] * 6 + $x[5] * 7 + $x[6] * 8 + $x[7] * 9 + $x[8] * 2 + $x[9] * 3 + $x[10] * 4 + $x[11] * 5;
        };

        $sum = $closure($c);
        $d1 = 11 - ($sum % 11);
        $d1 = $d1 >= 10 ? 0 : $d1;
        $c = $d1.$c;

        $sum = $closure($c) + $c[12] * 6;

        $d2 = 11 - ($sum % 11);
        $d2 = $d2 >= 10 ? 0 : $d2;

        return $d1.$d2;
    }

    public function fake(): string
    {
        $cnpj = rand(10000000, 99999999).'000'.rand(1, 9);

        return $cnpj.$this->checkDigits($cnpj);
    }

    public function label(): string
    {
        return 'CNPJ';
    }

    public function mask(string $document): string
    {
        $document = Regex::onlyNumeric($document);
        throw_if(strlen($document) !== 14, Exception::class, 'Invalid CNPJ');

        return Str::applyMask($document, '00.000.000/0000-00');
    }

    public function unmask(string $document): string
    {
        return Regex::onlyNumeric($document);
    }

    public function validate(string $document): bool
    {
        $document = Regex::onlyNumeric($document);
        if (strlen($document) != 14 || Regex::isSequenceOfUniqueChar($document)) {
            return false;
        }

        return substr($document, -2, 2) === $this->checkDigits($document);
    }
}
