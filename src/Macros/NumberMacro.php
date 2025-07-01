<?php

namespace Laraveltoolkit\Macros;

use BcMath\Number as BcNumber;
use Illuminate\Support\Number;
use RoundingMode;

class NumberMacro
{
    public function __invoke(): void
    {
        $this->spellCurrency();
        $this->roundAsMultipleOf();
    }

    public function spellCurrency(): void
    {
        Number::macro('spellCurrency', function (
            int|float $number,
            ?string $in = null,
            ?string $locale = null
        ): string {
            $int = intval(floor($number));
            $decimals = intval(floor((($number - $int) * 100)));

            $locale = $locale ?? app()->getLocale();

            $in = $in ?? Number::defaultCurrency();

            $singularKey = "laraveltoolkit::number.currencies.{$in}.singular";
            $pluralKey = "laraveltoolkit::number.currencies.{$in}.plural";

            $singularName = str(__($singularKey))->replace($singularKey, $in)->toString();
            $pluralName = str(__($pluralKey))->replace($pluralKey, $in)->toString();

            $spelled = [];
            $spelled[] = Number::spell($int, $locale);
            $spelled[] = ($int !== 1 ? $pluralName : $singularName);
            if ($decimals !== 0) {
                $spelled[] = __('laraveltoolkit::number.and');
                $spelled[] = Number::spell($decimals, $locale);
                $spelled[] = ($decimals !== 1 ? __('laraveltoolkit::number.cents') : __('laraveltoolkit::number.cent'));
            }

            return implode(' ', $spelled);
        });
    }

    public function roundAsMultipleOf(): void
    {
        Number::macro(
            'roundAsMultipleOf',
            function (
                float $value,
                float $step,
                RoundingMode $roundMode,
                ?float $min = null,
                ?float $max = null,
            ): BcNumber {
                $value = floatToBcNumber($value);
                $min = is_float($min) ? floatToBcNumber(strval($min)) : null;
                $max = is_float($max) ? floatToBcNumber(strval($max)) : null;
                $step = floatToBcNumber(strval($step));

                $value = $value
                    ->div($step)
                    ->round(0, $roundMode)
                    ->mul($step);

                return match (true) {
                    $min instanceof BcNumber && $value < $min => $min,
                    $max instanceof BcNumber && $value > $max => $max,
                    default => $value
                };
            }
        );
    }
}
