<?php

namespace Laraveltoolkit\Measurement\Concerns;

use BcMath\Number;

trait ConvertsLinearUnit
{
    public function fromReference(Number $referenceValue, int $scale): Number
    {
        return $referenceValue->div($this->reference(), $scale);
    }

    public function toReference(Number $value): Number
    {
        return $value->mul($this->reference());
    }

    public function toReferenceDelta(Number $value): Number
    {
        return $this->toReference($value);
    }
}
