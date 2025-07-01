<?php

namespace Laraveltoolkit\Support\Phone;

use Illuminate\Support\Str;
use Laraveltoolkit\Facades\Regex;

class NonRegional implements Phone
{
    public function appearsToBe(string $phone): string
    {
        return preg_match('/^0[3589]00$/', substr(Regex::onlyNumeric($phone), 0, 4)) === 1;
    }

    public function fake(): string
    {
        return sprintf(
            '0%s00%s',
            collect([3, 5, 8, 9])->shuffle()->first(),
            str_pad(rand(0, 9999999), 7, '0', STR_PAD_LEFT),
        );
    }

    public function label(): string
    {
        return 'Telefone não regional';
    }

    public function mask(string $phone): string
    {
        $phone = Regex::onlyNumeric($phone);

        return Str::applyMask($phone, '0000-000-0000');
    }

    public function unmask(string $phone): string
    {
        return Regex::onlyNumeric($phone);
    }

    public function validate(string $phone): bool
    {
        $phone = Regex::onlyNumeric($phone);

        return preg_match('/^0[3589]00[0-9]{7}$/', $phone) === 1;
    }
}
