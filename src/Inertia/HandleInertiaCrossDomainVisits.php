<?php

namespace Laraveltoolkit\Inertia;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

/**
 * Inspired on Robert Boes implementation
 *
 * @link https://robertbo.es/2024/navigating-between-sub-domains-with-inertia
 */
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
            $request->host() === $this->previousHost() => $next($request),
            default => Inertia::location($request->fullUrl())
        };
    }

    protected function previousHost(): string
    {
        return parse_url(url()->previous(), PHP_URL_HOST);
    }
}
