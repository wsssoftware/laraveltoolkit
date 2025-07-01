<?php

use BcMath\Number;

if (! function_exists('floatToBcNumber')) {
    /**
     * Converts a floating-point number to a BC Math compatible Number object.
     *
     * @param  float  $number  The floating-point number to convert.
     * @return Number The resulting Number object after conversion.
     */
    function floatToBcNumber(float $number): Number
    {
        $numberString = strval($number);

        $numberString = ! str_contains($numberString, 'E')
            ? $numberString
            : rtrim(number_format($number, ini_get('precision'), '.', ''), '0');

        return new Number($numberString);
    }
}
