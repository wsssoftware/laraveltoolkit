<?php

namespace Laraveltoolkit\DataAdapter;

use Illuminate\Support\Arr;

readonly class Constraint
{
    protected function __construct(
        public MatchMode $matchMode,
        public mixed $value,
    ) {
        //
    }

    public static function create(array $constraint): ?self
    {
        $matchMode = MatchMode::tryFrom(Arr::get($constraint, 'matchMode'));
        $value = Arr::get($constraint, 'value');

        return $matchMode !== null && (is_bool($value) || !empty($value)) ? new self($matchMode, $value) : null;
    }
}
