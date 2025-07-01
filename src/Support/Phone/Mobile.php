<?php

namespace Laraveltoolkit\Support\Phone;

use Illuminate\Support\Str;
use Laraveltoolkit\Facades\Regex;

class Mobile implements Phone
{
    public function appearsToBe(string $phone): string
    {
        return preg_match('/^[1-9][0-9]9$/', substr(Regex::onlyNumeric($phone), 0, 3)) === 1;
    }

    public function fake(): string
    {
        return sprintf(
            '%s9%s%s',
            rand(10, 99),
            rand(5000, 9999),
            str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT),
        );
    }

    public function label(): string
    {
        return 'Telefone móvel';
    }

    public function mask(string $phone): string
    {
        $phone = Regex::onlyNumeric($phone);

        return Str::applyMask($phone, '(00) 0 0000-0000');
    }

    public function unmask(string $phone): string
    {
        return Regex::onlyNumeric($phone);
    }

    public function validate(string $phone): bool
    {
        $phone = Regex::onlyNumeric($phone);

        return preg_match('/^[1-9][0-9]9[5-9][0-9]{7}$/', $phone) === 1;
    }
}
