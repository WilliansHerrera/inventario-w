<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyGlobalSettings
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = get_global_setting('locale');
        $key = config('moonshine.locale_key', '_lang');

        if ($request->hasSession() && ! $request->session()->has($key) && $locale) {
            $request->session()->put($key, $locale);
        }

        $currentLocale = $request->hasSession() 
            ? $request->session()->get($key, $locale ?: config('app.locale'))
            : ($locale ?: config('app.locale'));
        
        if ($currentLocale) {
            app()->setLocale($currentLocale);
            config(['app.locale' => $currentLocale]);
            config(['moonshine.locale' => $currentLocale]);
        }

        return $next($request);
    }
}
