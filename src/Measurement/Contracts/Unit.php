<?php

namespace Laraveltoolkit\Measurement\Contracts;

use BcMath\Number;

interface Unit
{
    public function dimension(): string;

    public function formattedName(int|float $value, ?string $locale = null): string;

    public function fromReference(Number $referenceValue, int $scale): Number;

    public function name(?string $locale = null): string;

    /**
     * Number of canonical reference units represented by this unit.
     */
    public function reference(): Number;

    public function term(?string $locale = null): string;

    public function toReference(Number $value): Number;

    public function toReferenceDelta(Number $value): Number;

    public function value(): string;
}
