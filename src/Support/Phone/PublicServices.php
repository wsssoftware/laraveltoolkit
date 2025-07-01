<?php

namespace Laraveltoolkit\Support\Phone;

use Illuminate\Support\Str;
use Laraveltoolkit\Facades\Regex;

class PublicServices implements Phone
{
    public function appearsToBe(string $phone): string
    {
        return preg_match('/^1[0-9]{2}$/', substr(Regex::onlyNumeric($phone), 0, 3)) === 1;
    }

    public function fake(): string
    {
        return sprintf(
            '1%s',
            str_pad(rand(0, 99), 2, '0', STR_PAD_LEFT),
        );
    }

    public function label(): string
    {
        return 'Telefone de serviços públicos';
    }

    public function mask(string $phone): string
    {
        $phone = Regex::onlyNumeric($phone);

        return Str::applyMask($phone, '000');
    }

    public function unmask(string $phone): string
    {
        return Regex::onlyNumeric($phone);
    }

    public function validate(string $phone): bool
    {
        $phone = Regex::onlyNumeric($phone);

        return preg_match('/^1[0-9]{2}$/', $phone) === 1;
    }
}
