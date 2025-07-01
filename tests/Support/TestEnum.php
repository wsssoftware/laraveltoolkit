<?php

namespace Laraveltoolkit\Tests\Support;

use Illuminate\Contracts\Support\Arrayable;

enum TestEnum: string implements Arrayable
{
    case A = 'a';
    case B = 'b';
    case C = 'c';

    public function toArray(): array
    {
        return [
            'id' => $this->value,
        ];
    }
}
