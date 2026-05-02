<?php

declare(strict_types=1);

namespace App\MoonShine\Layouts;

use App\MoonShine\Pages\BackupPage;
use App\MoonShine\Pages\BarcodePrintingPage;
use App\MoonShine\Pages\POS;
use App\MoonShine\Pages\SystemUpdatePage;
use App\MoonShine\Resources\Caja\CajaResource;
use App\MoonShine\Resources\CajaTurno\CajaTurnoResource;
use App\MoonShine\Resources\Compra\CompraResource;
use App\MoonShine\Resources\GlobalSetting\GlobalSettingResource;
use App\MoonShine\Resources\Inventario\InventarioResource;
use App\MoonShine\Resources\Locale\LocaleResource;
use App\MoonShine\Resources\Producto\ProductoResource;
use App\MoonShine\Resources\ProductoCostoHistorial\ProductoCostoHistorialResource;
use App\MoonShine\Resources\Proveedor\ProveedorResource;
use App\MoonShine\Resources\Venta\VentaResource;
use App\MoonShine\Resources\VentaDetalle\VentaDetalleResource;
use MoonShine\AssetManager\Css;
use MoonShine\ColorManager\Palettes\PurplePalette;
use MoonShine\Contracts\ColorManager\ColorManagerContract;
use MoonShine\Contracts\ColorManager\PaletteContract;
use MoonShine\Laravel\Layouts\AppLayout;
use MoonShine\Laravel\Resources\MoonShineUserResource;
use MoonShine\Laravel\Resources\MoonShineUserRoleResource;
use MoonShine\MenuManager\MenuDivider;
use MoonShine\MenuManager\MenuGroup;
use MoonShine\MenuManager\MenuItem;
use MoonShine\UI\Components\Layout\Div;
use MoonShine\UI\Components\Layout\Logo;

final class MoonShineLayout extends AppLayout
{
    public function getPalette(): string
    {
        return get_global_setting('theme_palette', PurplePalette::class);
    }

    protected function getLogoComponent(): Logo
    {
        return Logo::make(
            href: route($this->getCore()->getConfig()->getHomeRoute()),
            logo: route('branding.logo-light'), // Standard (Light mode)
            logoSmall: route('branding.logo-light'),
        )->darkMode(
            logo: route('branding.logo'),      // Dark mode
            small: route('branding.logo')
        );
    }

    protected function getLogo(bool $small = false): string
    {
        return route('branding.logo');
    }

    protected function assets(): array
    {
        return [
            ...parent::assets(),
            Css::make('/vendor/moonshine/assets/custom-sidebar.css?v='.time()),
        ];
    }

    protected function sidebarTopSlot(): array
    {
        return [
            Div::make([
                // Solo Resize Handler
                Div::make()
                    ->class('sidebar-resizer')
                    ->customAttributes([
                        '@mousedown' => 'dragStart($event)',
                        ':class' => '{ "is-dragging": isDragging }',
                    ]),
            ])
                ->customAttributes([
                    'x-data' => '{
                    sidebarWidth: $persist(230).as("sb_width"),
                    isDragging: false,
                    dragStart(e) {
                        this.isDragging = true;
                        this._dragging = this.dragging.bind(this);
                        this._dragEnd = this.dragEnd.bind(this);
                        document.addEventListener("mousemove", this._dragging);
                        document.addEventListener("mouseup", this._dragEnd);
                    },
                    dragging(e) {
                        if(!this.isDragging) return;
                        let newWidth = e.clientX;
                        if(newWidth > 180 && newWidth < 600) {
                            this.sidebarWidth = newWidth;
                            document.documentElement.style.setProperty("--sidebar-real-width", newWidth + "px");
                        }
                    },
                    dragEnd() {
                        this.isDragging = false;
                        document.removeEventListener("mousemove", this._dragging);
                        document.removeEventListener("mouseup", this._dragEnd);
                    }
                }',
                    'x-init' => '
                    $nextTick(() => {
                        document.documentElement.style.setProperty("--sidebar-real-width", sidebarWidth + "px");
                    })
                ',
                ]),
        ];
    }

    protected function menu(): array
    {
        $mode = get_global_setting('cash_management_mode', 'express');

        $ventasCajaItems = [
            MenuItem::make(POS::class, __('Punto de Venta'))->icon('computer-desktop'),
            MenuItem::make(CajaResource::class, __('Control de Cajas'))->icon('rectangle-group'),
            MenuItem::make(VentaResource::class, __('Registros de Ventas'))->icon('shopping-cart'),
            MenuItem::make(VentaDetalleResource::class, __('Historial Detallado'))->icon('list-bullet'),
        ];

        if ($mode !== 'express') {
            $ventasCajaItems[] = MenuItem::make(CajaTurnoResource::class, __('Auditoría de Turnos'))->icon('calculator');
        }

        $configItems = [
            MenuDivider::make(__('Preferencias Regionales')),
        ];

        if ($mode !== 'express') {
            $configItems[] = MenuItem::make(LocaleResource::class, __('Sucursales / Locales'))->icon('map-pin');
        }

        $configItems[] = MenuItem::make(GlobalSettingResource::class, __('Ajustes del Sistema'))->icon('globe-alt');

        return [
            MenuGroup::make(__('Ventas & Caja'), $ventasCajaItems)->icon('banknotes'),

            MenuGroup::make(__('Inventario'), [
                MenuItem::make(ProductoResource::class, __('Catálogo de Productos'))->icon('tag'),
                MenuItem::make(InventarioResource::class, __('Control de Stock'))->icon('archive-box'),
                MenuItem::make(BarcodePrintingPage::class, __('Imprimir Viñetas'))->icon('qr-code'),
                MenuItem::make(CompraResource::class, __('Recepción de Compras'))->icon('shopping-bag'),
                MenuItem::make(ProveedorResource::class, __('Proveedores'))->icon('users'),
                MenuItem::make(ProductoCostoHistorialResource::class, __('Historial de Costos'))->icon('presentation-chart-line'),
            ])->icon('building-storefront'),

            MenuGroup::make(__('Configuración'), array_merge($configItems, [
                MenuDivider::make(__('Seguridad y Accesos')),
                MenuItem::make(MoonShineUserResource::class, __('Usuarios Administrativos'))->icon('users'),
                MenuItem::make(MoonShineUserRoleResource::class, __('Roles y Permisos'))->icon('shield-check'),

                MenuDivider::make(__('Sistema y Mantenimiento')),
                MenuItem::make(SystemUpdatePage::class, __('Actualizaciones (Web/POS)'))->icon('cloud-arrow-up'),
                MenuItem::make(BackupPage::class, __('Copias de Seguridad (Backup)'))->icon('circle-stack'),
                MenuItem::make(fn () => route('admin.pos.download'), __('Descargar Terminal POS (EXE)'))
                    ->icon('arrow-down-tray')
                    ->blank(),
            ]))->icon('cog-6-tooth'),
        ];
    }

    protected function colors(ColorManagerContract $colorManager): void
    {
        parent::colors($colorManager);

        $paletteClass = get_global_setting('theme_palette', PurplePalette::class);

        if (class_exists($paletteClass)) {
            $palette = new $paletteClass;
            if ($palette instanceof PaletteContract) {
                $colorManager->palette($palette);
            }
        }
    }

    protected function getFooterCopyright(): string
    {
        return \sprintf(
            <<<'HTML'
                &copy; %d
                <a href="#" class="font-semibold text-primary" target="_blank">
                    Willians Herrera
                </a>
                - Inventario-W. Todos los derechos reservados.
            HTML,
            now()->year,
        );
    }
}
