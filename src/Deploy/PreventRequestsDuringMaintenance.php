<?php

namespace Laraveltoolkit\Deploy;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;

class PreventRequestsDuringMaintenance extends Middleware
{
    /**
     * The URIs that should be reachable while maintenance mode is enabled.
     *
     * @var array<int, string>
     */
    protected $except = [];

    public function __construct(Application $app)
    {
        $this->except[] = config('laraveltoolkit.deploy.path');
        parent::__construct($app);
    }
}
