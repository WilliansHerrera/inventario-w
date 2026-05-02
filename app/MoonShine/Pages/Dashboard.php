<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\Models\Inventario;
use App\Models\Venta;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Laravel\Pages\Page;
use MoonShine\UI\Components\Badge;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Components\Metrics\Wrapped\ValueMetric;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\UI\Fields\Text;

class Dashboard extends Page
{
    public function getBreadcrumbs(): array
    {
        return [
            '#' => $this->getTitle(),
        ];
    }

    public function getTitle(): string
    {
        return __($this->title ?: 'Panel de Control Industrial');
    }

    /**
     * @return list<ComponentContract>
     */
    protected function components(): iterable
    {
        // 1. Datos para Métricas
        $ventasHoy = Venta::whereDate('created_at', Carbon::today())->sum('total');
        $ventasMes = Venta::whereMonth('created_at', Carbon::now()->month)->sum('total');
        $operacionesHoy = Venta::whereDate('created_at', Carbon::today())->count();
        $productosBajos = Inventario::where('stock', '<=', 5)->count();

        // Nuevas Métricas Financieras
        $inversionInventario = Inventario::join('productos', 'inventarios.producto_id', '=', 'productos.id')
            ->selectRaw('SUM(inventarios.stock * productos.precio) as total')
            ->value('total') ?? 0;

        $gananciaMes = DB::table('venta_detalles')
            ->join('ventas', 'venta_detalles.venta_id', '=', 'ventas.id')
            ->join('productos', 'venta_detalles.producto_id', '=', 'productos.id')
            ->whereMonth('ventas.created_at', Carbon::now()->month)
            ->selectRaw('SUM(venta_detalles.cantidad * (venta_detalles.precio_unitario - productos.precio)) as total')
            ->value('total') ?? 0;

        // 2. Datos para Tablas
        $recentSales = Venta::orderBy('created_at', 'desc')->limit(5)->get();
        $stockAlerts = Inventario::with('producto')
            ->where('stock', '<=', 5)
            ->orderBy('stock', 'asc')
            ->limit(5)
            ->get();

        return [
            Grid::make([
                // Fila 1: Centro de Métricas Financieras
                Column::make([
                    ValueMetric::make(__('Ventas Hoy'))
                        ->value(format_currency($ventasHoy))
                        ->icon('banknotes'),
                ])->columnSpan(3),

                Column::make([
                    ValueMetric::make(__('Ventas Mes'))
                        ->value(format_currency($ventasMes))
                        ->icon('chart-bar'),
                ])->columnSpan(3),

                Column::make([
                    ValueMetric::make(__('Ganancia Mes'))
                        ->value(format_currency($gananciaMes))
                        ->icon('currency-dollar'),
                ])->columnSpan(3),

                Column::make([
                    ValueMetric::make(__('Tickets del Día'))
                        ->value($operacionesHoy)
                        ->icon('ticket'),
                ])->columnSpan(3),

                Column::make([
                    ValueMetric::make(__('Capital en Stock'))
                        ->value(format_currency($inversionInventario))
                        ->icon('archive-box'),
                ])->columnSpan(6),

                Column::make([
                    ValueMetric::make(__('Stock Crítico'))
                        ->value($productosBajos)
                        ->icon('archive-box-x-mark'),
                ])->columnSpan(6),

                // Fila 2: Análisis Detallado
                Column::make([
                    Box::make(__('Últimas Ventas POS'), [
                        TableBuilder::make()
                            ->items($recentSales)
                            ->fields([
                                Text::make(__('Fecha'), 'created_at', fn ($item) => $item->created_at->format('H:i:s')),
                                Text::make(__('Método'), 'metodo_pago', fn ($item) => Badge::make($item->metodo_pago, $item->metodo_pago === 'efectivo' ? 'success' : 'info')),
                                Text::make(__('Total'), 'total', fn ($item) => format_currency($item->total)),
                            ]),
                    ]),
                ])->columnSpan(6),

                Column::make([
                    Box::make(__('Alertas de Resurtido'), [
                        TableBuilder::make()
                            ->items($stockAlerts)
                            ->fields([
                                Text::make(__('Producto'), 'producto.nombre'),
                                Text::make(__('Stock'), 'stock', fn ($item) => Badge::make((string) $item->stock, $item->stock <= 0 ? 'error' : 'warning')),
                            ]),
                    ]),
                ])->columnSpan(6),
            ]),
        ];
    }
}
