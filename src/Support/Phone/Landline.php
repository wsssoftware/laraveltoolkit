<?php

namespace Laraveltoolkit\Support\Phone;

use Illuminate\Support\Str;
use Laraveltoolkit\Facades\Regex;

class Landline implements Phone
{
    public function appearsToBe(string $phone): string
    {
        $phone = Regex::onlyNumeric($phone);

        return preg_match('/^[1-9][0-9][1-5][0-9]$/', substr($phone, 0, 4)) === 1;
    }

    public function fake(): string
    {
        return sprintf(
            '%s%s%s',
            rand(10, 99),
            rand(1000, 5999),
            str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT),
        );
    }

    public function label(): string
    {
        return 'Telefone fixo';
    }

    public function mask(string $phone): string
    {
        $phone = Regex::onlyNumeric($phone);

        return Str::applyMask($phone, '(00) 0000-0000');
    }

    public function unmask(string $phone): string
    {
        return Regex::onlyNumeric($phone);
    }

    public function validate(string $phone): bool
    {
        $phone = Regex::onlyNumeric($phone);

        return preg_match('/^[1-9][0-9][1-5][0-9]{7}$/', $phone) === 1;
    }
}
