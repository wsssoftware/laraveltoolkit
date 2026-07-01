<?php

namespace Laraveltoolkit\Tests\Enum;

use Laraveltoolkit\Enum\ArrayableEnum;
use Laraveltoolkit\Enum\HasArrayableEnum;

enum FakeSortableEnum: string implements ArrayableEnum
{
    use HasArrayableEnum;

    case SECOND = 'second';
    case FIRST = 'first';
    case THIRD = 'third';

    public function label(): string
    {
        return match ($this) {
            self::FIRST => 'Alpha',
            self::SECOND => 'Beta',
            self::THIRD => 'Gamma',
        };
    }
}
