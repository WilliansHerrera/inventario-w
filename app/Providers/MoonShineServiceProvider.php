<?php

declare(strict_types=1);

namespace App\Providers;

use App\MoonShine\Pages\BackupPage;
use App\MoonShine\Pages\BarcodePrintingPage;
use App\MoonShine\Pages\POS;
use App\MoonShine\Pages\ResetPage;
use App\MoonShine\Pages\SystemUpdatePage;
use App\MoonShine\Resources\Caja\CajaResource;
use App\MoonShine\Resources\CajaTurno\CajaTurnoResource;
use App\MoonShine\Resources\Compra\CompraResource;
use App\MoonShine\Resources\CompraDetalle\CompraDetalleResource;
use App\MoonShine\Resources\GlobalSetting\GlobalSettingResource;
use App\MoonShine\Resources\Inventario\InventarioResource;
use App\MoonShine\Resources\InventarioMovimientoResource;
use App\MoonShine\Resources\Locale\LocaleResource;
use App\MoonShine\Resources\MoonShineUser\MoonShineUserResource;
use App\MoonShine\Resources\MoonShineUserRole\MoonShineUserRoleResource;
use App\MoonShine\Resources\Producto\ProductoResource;
use App\MoonShine\Resources\ProductoCostoHistorial\ProductoCostoHistorialResource;
use App\MoonShine\Resources\Proveedor\ProveedorResource;
use App\MoonShine\Resources\Venta\VentaResource;
use App\MoonShine\Resources\VentaDetalle\VentaDetalleResource;
use Illuminate\Support\ServiceProvider;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Laravel\DependencyInjection\MoonShineConfigurator;

class MoonShineServiceProvider extends ServiceProvider
{
    /**
     * @param  CoreContract<MoonShineConfigurator>  $core
     */
    public function boot(CoreContract $core): void
    {
        $core
            ->resources([
                MoonShineUserResource::class,
                MoonShineUserRoleResource::class,
                LocaleResource::class,
                ProductoResource::class,
                InventarioResource::class,
                CajaResource::class,
                VentaResource::class,
                VentaDetalleResource::class,
                GlobalSettingResource::class,
                CajaTurnoResource::class,
                InventarioMovimientoResource::class,
                ProveedorResource::class,
                CompraResource::class,
                ProductoCostoHistorialResource::class,
                CompraDetalleResource::class,
            ])
            ->pages([
                ...$core->getConfig()->getPages(),
                POS::class,
                BackupPage::class,
                SystemUpdatePage::class,
                BarcodePrintingPage::class,
                ResetPage::class,
            ]);
    }
}
