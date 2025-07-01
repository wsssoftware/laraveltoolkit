<?php

namespace Laraveltoolkit\Inertia;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class HandleInertiaCrossDomainVisits
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->validVisit($request)) {
            return $next($request);
        }
        return Inertia::location($request->fullUrl());
    }

    public function validVisit(Request $request): bool
    {
        if ($request->headers->has('X-Inertia')) {
            return true;
        }
        if (!$request->isMethod($request::METHOD_GET)) {
            return true;
        }
        if ($request->host() === parse_url(url()->previous(), PHP_URL_HOST)) {
            return true;
        }

        return true;
    }
}