<?php

namespace Laraveltoolkit\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see Laraveltoolkit\Laraveltoolkit
 */
class Laraveltoolkit extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Laraveltoolkit\Laraveltoolkit::class;
    }
}
