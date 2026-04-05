<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetRegionalSettings
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = get_global_setting('locale', config('app.locale'));
        app()->setLocale($locale);

        // Compartir el símbolo de moneda en todas las vistas
        view()->share('currency_symbol', get_currency_symbol());

        return $next($request);
    }
}
