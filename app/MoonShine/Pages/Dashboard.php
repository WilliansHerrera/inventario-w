<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use MoonShine\Laravel\Pages\Page;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Components\Metrics\Wrapped\ValueMetric;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Box;
use App\Models\Venta;
use App\Models\Inventario;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
#[\MoonShine\MenuManager\Attributes\SkipMenu]

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
        return $this->title ?: 'Panel de Control - POS';
    }

    /**
     * @return list<ComponentContract>
     */
    protected function components(): iterable
    {
        $ventasHoy = Venta::whereDate('created_at', Carbon::today())->sum('total');
        $ventasMes = Venta::whereMonth('created_at', Carbon::now()->month)->sum('total');
        $operacionesHoy = Venta::whereDate('created_at', Carbon::today())->count();
        $productosBajos = Inventario::where('stock', '<=', 5)->count();

        return [
            Grid::make([
                Column::make([
                    ValueMetric::make('Ventas de Hoy')
                        ->value(format_currency($ventasHoy))
                        ->icon('banknotes')
                ])->columnSpan(3),

                Column::make([
                    ValueMetric::make('Ventas del Mes')
                        ->value(format_currency($ventasMes))
                        ->icon('chart-bar')
                ])->columnSpan(3),

                Column::make([
                    ValueMetric::make('Tickets Generados (Hoy)')
                        ->value($operacionesHoy)
                        ->icon('users')
                ])->columnSpan(3),

                Column::make([
                    ValueMetric::make('Alertas Críticas')
                        ->value($productosBajos . ' Productos sin stock')
                        ->icon('archive-box-x-mark')
                ])->columnSpan(3),
            ]),
        ];
    }
}
