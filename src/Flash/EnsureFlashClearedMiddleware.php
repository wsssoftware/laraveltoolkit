<?php

namespace Laraveltoolkit\Flash;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Symfony\Component\HttpFoundation\Response;

class EnsureFlashClearedMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    /**
     * Handle tasks after the response has been sent to the browser.
     */
    public function terminate(Request $request, Response $response): void
    {
        usleep(500000);
        $id = session()->getId();
        $session = app(SessionManager::class);
        $session->flush();
        $session->setId($id);
        $session->start();

        if (! empty($session->get(Flash::SESSION_KEY))) {
            \Laraveltoolkit\Facades\Flash::clear();
        }
    }
}
