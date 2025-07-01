<?php

namespace Laraveltoolkit\Support\Document;

use Exception;
use Illuminate\Support\Str;
use Laraveltoolkit\Facades\Regex;

class CPF implements Document
{
    public function checkDigits(string $document): string
    {
        $document = Regex::onlyNumeric($document);
        if (strlen($document) > 9) {
            $document = substr($document, 0, 9);
        } elseif (strlen($document) < 9) {
            $document = str_pad($document, 9, '0');
        }
        $sum = $document[0] * 10 + $document[1] * 9 + $document[2] * 8 + $document[3] * 7 + $document[4] * 6 + $document[5] * 5 + $document[6] * 4 + $document[7] * 3 + $document[8] * 2;
        $d1 = 11 - ($sum % 11);
        $d1 = $d1 >= 10 ? 0 : $d1;
        $document .= $d1;

        $sum = $document[0] * 11 + $document[1] * 10 + $document[2] * 9 + $document[3] * 8 + $document[4] * 7 + $document[5] * 6 + $document[6] * 5 + $document[7] * 4 + $document[8] * 3 + $document[9] * 2;
        $d2 = 11 - ($sum % 11);
        $d2 = $d2 >= 10 ? 0 : $d2;

        return $d1.$d2;
    }

    public function fake(): string
    {
        $cpf = str_pad(rand(10000000, 99999999), 9, '0', STR_PAD_LEFT);

        return $cpf.$this->checkDigits($cpf);
    }

    public function label(): string
    {
        return 'CPF';
    }

    public function mask(string $document): string
    {
        $document = Regex::onlyNumeric($document);
        throw_if(strlen($document) !== 11, Exception::class, 'Invalid CPF');

        return Str::applyMask($document, '000.000.000-00');
    }

    public function unmask(string $document): string
    {
        return Regex::onlyNumeric($document);
    }

    public function validate(string $document): bool
    {
        $document = Regex::onlyNumeric($document);
        if (strlen($document) != 11 || Regex::isSequenceOfUniqueChar($document)) {
            return false;
        }

        return substr($document, -2, 2) === $this->checkDigits($document);
    }
}
