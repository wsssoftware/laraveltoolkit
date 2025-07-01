<?php

namespace Laraveltoolkit\Deploy;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response|Responsable
    {
        if (! app()->isDownForMaintenance()) {
            return redirect(request()->query('redirect') ?? config('laraveltoolkit.deploy.default_redirect'));
        }

        return Inertia::render(config('laraveltoolkit.deploy.inertia_component'));
    }
}
