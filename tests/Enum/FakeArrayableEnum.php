<?php

namespace Laraveltoolkit\Tests\Enum;

use Illuminate\Contracts\Support\Arrayable;
use Laraveltoolkit\Enum\HasArrayableEnum;

enum FakeArrayableEnum: string implements Arrayable
{
    use HasArrayableEnum;

    case FIRST = 'first';
    case SECOND = 'second';
    case THIRD = 'third';

    public function toArray(): array
    {
        return match ($this) {
            self::FIRST => ['label' => 'Zulu', 'name' => 'First'],
            self::SECOND => ['label' => 'Alpha', 'name' => 'Second'],
            self::THIRD => ['label' => 'Mike', 'name' => 'Third'],
        };
    }
}
