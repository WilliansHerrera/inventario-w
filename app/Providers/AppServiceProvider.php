<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Observers\VentaObserver;
use App\Observers\VentaDetalleObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('Helpers/settings.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Venta::observe(VentaObserver::class);
        VentaDetalle::observe(VentaDetalleObserver::class);
    }
}
