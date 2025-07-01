<?php

namespace Laraveltoolkit\Macros;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;

class RouterMacro
{
    public function __invoke(): void
    {
        $this->getAndPost();
    }

    public function getAndPost(): void
    {
        Router::macro('getAndPost', function ($uri, $action): Route {
            return $this->addRoute(['HEAD', 'GET', 'POST'], $uri, $action);
        });
    }
}
