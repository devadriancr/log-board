<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shows the site in the language the visitor chose (kept in the session). Without a choice it is Spanish.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->hasSession() ? $request->session()->get('locale') : null;

        if (is_string($locale) && array_key_exists($locale, config('app.locales'))) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
