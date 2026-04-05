<?php

declare(strict_types=1);

namespace App\MoonShine\Layouts;

use MoonShine\Laravel\Layouts\AppLayout as BaseLayout;
use MoonShine\MenuManager\MenuGroup;
use MoonShine\MenuManager\MenuItem;
use MoonShine\Laravel\Resources\MoonShineUserResource;
use MoonShine\Laravel\Resources\MoonShineUserRoleResource;
use App\MoonShine\Resources\Locale\LocaleResource;
use App\MoonShine\Resources\Producto\ProductoResource;
use App\MoonShine\Resources\Inventario\InventarioResource;
use App\MoonShine\Resources\Caja\CajaResource;

class AppLayout extends BaseLayout
{
    protected function menu(): array
    {
        return [
            MenuGroup::make('Inventario', [
                MenuItem::make(ProductoResource::class, 'Productos')->icon('shopping-bag'),
                MenuItem::make(InventarioResource::class, 'Stock por Local')->icon('archive-box'),
            ])->icon('building-storefront'),

            MenuGroup::make('Configuración', [
                MenuItem::make(LocaleResource::class, 'Sucursales')->icon('map-pin'),
                MenuItem::make(CajaResource::class, 'Cajas')->icon('rectangle-stack'),
            ])->icon('cog-6-tooth'),

            MenuGroup::make('Sistema', [
                MenuItem::make(MoonShineUserResource::class, 'Usuarios')->icon('users'),
                MenuItem::make(MoonShineUserRoleResource::class, 'Roles')->icon('shield-check'),
            ])->icon('swatch'),
        ];
    }
}
