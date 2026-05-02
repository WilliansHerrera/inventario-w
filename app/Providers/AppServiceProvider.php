<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 🛡️ Prevent lazy loading in non-production environments
        // Model::preventLazyLoading(!app()->isProduction());

        // 🛡️ Configure Rate Limiting for critical POS actions
        RateLimiter::for('pos-sales', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });

        $path = parse_url(config('app.url'), PHP_URL_PATH);

        if ($path) {
            Livewire::setUpdateRoute(function ($handle) use ($path) {
                return Route::post($path.'/livewire/update', $handle);
            });

            Livewire::setScriptRoute(function ($handle) use ($path) {
                return Route::get($path.'/livewire/livewire.js', $handle);
            });
        }
    }
}
