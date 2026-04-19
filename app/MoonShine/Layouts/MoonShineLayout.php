<?php

declare(strict_types=1);

namespace App\MoonShine\Layouts;

use MoonShine\Laravel\Layouts\AppLayout;
use MoonShine\ColorManager\Palettes\PurplePalette;
use MoonShine\ColorManager\ColorManager;
use MoonShine\AssetManager\Css;
use MoonShine\Contracts\ColorManager\ColorManagerContract;
use MoonShine\Contracts\ColorManager\PaletteContract;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\Layout\Div;
use MoonShine\MenuManager\MenuDivider;
use MoonShine\MenuManager\MenuGroup;
use MoonShine\MenuManager\MenuItem;
use App\MoonShine\Resources\Locale\LocaleResource;
use App\MoonShine\Resources\Producto\ProductoResource;
use App\MoonShine\Resources\Inventario\InventarioResource;
use App\MoonShine\Resources\Caja\CajaResource;
use MoonShine\Laravel\Resources\MoonShineUserResource;
use MoonShine\Laravel\Resources\MoonShineUserRoleResource;
use App\MoonShine\Resources\Venta\VentaResource;
use App\MoonShine\Resources\VentaDetalle\VentaDetalleResource;
use App\MoonShine\Resources\GlobalSetting\GlobalSettingResource;
use App\MoonShine\Resources\CajaTurno\CajaTurnoResource;
use App\MoonShine\Pages\SystemUpdatePage;
use App\MoonShine\Pages\POS;
use App\MoonShine\Pages\BackupPage;
use App\MoonShine\Resources\Compra\CompraResource;
use App\MoonShine\Resources\CompraDetalle\CompraDetalleResource;

final class MoonShineLayout extends AppLayout
{
    public function getPalette(): string
    {
        return get_global_setting('theme_palette', PurplePalette::class);
    }

    protected function assets(): array
    {
        return [
            ...parent::assets(),
            Css::make('/vendor/moonshine/assets/custom-sidebar.css?v=' . time()),
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
                        ':class' => '{ "is-dragging": isDragging }'
                    ])
            ])
            ->customAttributes([
                'x-data' => '{
                    sidebarWidth: $persist(200).as("sb_width"),
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
                '
            ])
        ];
    }

    protected function menu(): array
    {
        return [
            MenuGroup::make('Ventas & Caja', [
                MenuItem::make(POS::class, 'Punto de Venta')->icon('computer-desktop'),
                MenuItem::make(CajaResource::class, 'Control de Cajas')->icon('rectangle-group'),
                MenuItem::make(VentaResource::class, 'Registros de Ventas')->icon('shopping-cart'),
                MenuItem::make(VentaDetalleResource::class, 'Historial Detallado')->icon('list-bullet'),
                MenuItem::make(CajaTurnoResource::class, 'Auditoría de Turnos')->icon('calculator'),
            ])->icon('banknotes'),

            MenuGroup::make('Inventario', [
                MenuItem::make(ProductoResource::class, 'Catálogo de Productos')->icon('tag'),
                MenuItem::make(InventarioResource::class, 'Control de Stock')->icon('archive-box'),
                MenuItem::make(\App\MoonShine\Pages\BarcodePrintingPage::class, 'Imprimir Viñetas')->icon('qr-code'),
                MenuItem::make(CompraResource::class, 'Recepción de Compras')->icon('shopping-bag'),
                MenuItem::make(\App\MoonShine\Resources\Proveedor\ProveedorResource::class, 'Proveedores')->icon('users'),
                MenuItem::make(\App\MoonShine\Resources\ProductoCostoHistorial\ProductoCostoHistorialResource::class, 'Historial de Costos')->icon('presentation-chart-line'),
            ])->icon('building-storefront'),

            MenuGroup::make('Configuración', [
                MenuDivider::make('Preferencias Regionales'),
                MenuItem::make(LocaleResource::class, 'Sucursales / Locales')->icon('map-pin'),
                MenuItem::make(GlobalSettingResource::class, 'Ajustes del Sistema')->icon('globe-alt'),

                MenuDivider::make('Seguridad y Accesos'),
                MenuItem::make(MoonShineUserResource::class, 'Usuarios Administrativos')->icon('users'),
                MenuItem::make(MoonShineUserRoleResource::class, 'Roles y Permisos')->icon('shield-check'),

                MenuDivider::make('Sistema y Mantenimiento'),
                MenuItem::make(SystemUpdatePage::class, 'Actualizaciones (Web/POS)')->icon('cloud-arrow-up'),
                MenuItem::make(BackupPage::class, 'Copias de Seguridad (Backup)')->icon('circle-stack'),
                MenuItem::make(fn() => route('admin.pos.download'), 'Descargar Terminal POS (EXE)')
                    ->icon('arrow-down-tray')
                    ->blank(),
            ])->icon('cog-6-tooth'),
        ];
    }

    /**
     * @param ColorManagerContract $colorManager
     */
    protected function colors(ColorManagerContract $colorManager): void
    {
        parent::colors($colorManager);

        $paletteClass = get_global_setting('theme_palette', PurplePalette::class);
        
        if (class_exists($paletteClass)) {
            $palette = new $paletteClass();
            if ($palette instanceof \MoonShine\Contracts\ColorManager\PaletteContract) {
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
