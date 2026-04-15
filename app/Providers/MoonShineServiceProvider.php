<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use MoonShine\Contracts\Core\DependencyInjection\CoreContract;
use MoonShine\Laravel\DependencyInjection\MoonShine;
use MoonShine\Laravel\DependencyInjection\MoonShineConfigurator;
use App\MoonShine\Resources\MoonShineUser\MoonShineUserResource;
use App\MoonShine\Resources\MoonShineUserRole\MoonShineUserRoleResource;
use App\MoonShine\Resources\Locale\LocaleResource;
use App\MoonShine\Resources\Producto\ProductoResource;
use App\MoonShine\Resources\Inventario\InventarioResource;
use App\MoonShine\Resources\Caja\CajaResource;
use App\MoonShine\Resources\Venta\VentaResource;
use App\MoonShine\Resources\VentaDetalle\VentaDetalleResource;
use App\MoonShine\Resources\GlobalSetting\GlobalSettingResource;
use App\MoonShine\Resources\CajaTurnoResource;

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
            ])
            ->pages([
                ...$core->getConfig()->getPages(),
                \App\MoonShine\Pages\POS::class,
                \App\MoonShine\Pages\BackupPage::class,
                \App\MoonShine\Pages\SystemUpdatePage::class,
            ])
        ;
    }
}
