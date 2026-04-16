<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use MoonShine\Laravel\Pages\Page;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Components\Metrics\Wrapped\ValueMetric;
use MoonShine\UI\Components\Metrics\Wrapped\LineChartMetric;
use MoonShine\UI\Components\Metrics\Wrapped\DonutChartMetric;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Components\Badge;
use App\Models\Venta;
use App\Models\Inventario;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class Dashboard extends Page
{
    public function getBreadcrumbs(): array
    {
        return [
            '#' => $this->getTitle()
        ];
    }

    public function getTitle(): string
    {
        return $this->title ?: 'Panel de Control Industrial';
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

        // 2. Datos para Tablas
        $recentSales = Venta::orderBy('created_at', 'desc')->limit(5)->get();
        $stockAlerts = Inventario::with('producto')
            ->where('stock', '<=', 5)
            ->orderBy('stock', 'asc')
            ->limit(5)
            ->get();

        return [
            Grid::make([
                // Fila 1: Centro de Métricas
                Column::make([
                    ValueMetric::make('Ventas Hoy')
                        ->value(format_currency($ventasHoy))
                        ->icon('banknotes')
                ])->columnSpan(3),

                Column::make([
                    ValueMetric::make('Ventas Mes')
                        ->value(format_currency($ventasMes))
                        ->icon('chart-bar')
                ])->columnSpan(3),

                Column::make([
                    ValueMetric::make('Tickets del Día')
                        ->value($operacionesHoy)
                        ->icon('ticket')
                ])->columnSpan(3),

                Column::make([
                    ValueMetric::make('Stock Crítico')
                        ->value($productosBajos)
                        ->icon('archive-box-x-mark')
                ])->columnSpan(3),

                // Fila 2: Análisis Detallado
                Column::make([
                    Box::make('📝 Últimas Ventas POS', [
                        TableBuilder::make()
                            ->items($recentSales)
                            ->fields([
                                Text::make('Fecha', 'created_at', fn($item) => $item->created_at->format('H:i:s')),
                                Text::make('Método', 'metodo_pago', fn($item) => Badge::make($item->metodo_pago, $item->metodo_pago === 'efectivo' ? 'success' : 'info')),
                                Text::make('Total', 'total', fn($item) => format_currency($item->total)),
                            ])
                    ])
                ])->columnSpan(6),

                Column::make([
                    Box::make('⚠️ Alertas de Resurtido', [
                        TableBuilder::make()
                            ->items($stockAlerts)
                            ->fields([
                                Text::make('Producto', 'producto.nombre'),
                                Text::make('Stock', 'stock', fn($item) => Badge::make((string)$item->stock, $item->stock <= 0 ? 'error' : 'warning')),
                            ])
                    ])
                ])->columnSpan(6),
            ]),
        ];
    }
}
