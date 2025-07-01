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
        return match (true) {
            ! $request->headers->has('X-Inertia'),
            ! $request->isMethod($request::METHOD_GET),
            $request->host() === parse_url(url()->previous(), PHP_URL_HOST) => $next($request),
            default => Inertia::location($request->fullUrl())
        };

    }
}
