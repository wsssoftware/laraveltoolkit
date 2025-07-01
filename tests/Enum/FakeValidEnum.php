<?php

namespace Laraveltoolkit\Tests\Enum;

use Laraveltoolkit\Enum\ArrayableEnum;
use Laraveltoolkit\Enum\HasArrayableEnum;

enum FakeValidEnum: string implements ArrayableEnum
{
    use HasArrayableEnum;

    case OPTION_1 = 'option_1';
    case OPTION_2 = 'option_2';
    case OPTION_3 = 'option_3';
    case OPTION_4 = 'option_4';

}
