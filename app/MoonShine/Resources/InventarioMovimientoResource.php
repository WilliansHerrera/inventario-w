<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\InventarioMovimiento;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Date;

/**
 * @extends ModelResource<InventarioMovimiento>
 */
class InventarioMovimientoResource extends ModelResource
{
    protected string $model = InventarioMovimiento::class;

    protected string $title = 'Movimientos de Inventario';

    protected bool $isAsync = true;

    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Date::make('Fecha', 'created_at')
                ->format('d/m/Y H:i')
                ->sortable(),
            Text::make('Tipo', 'tipo')
                ->badge(fn($val) => match($val) {
                    'venta', 'salida' => 'red',
                    'entrada', 'compra' => 'green',
                    default => 'gray'
                }),
            Number::make('Cantidad', 'cantidad')
                ->sortable(),
            Text::make('Motivo', 'motivo'),
        ];
    }

    public function rules($item): array
    {
        return [
            'tipo' => ['required'],
            'cantidad' => ['required', 'numeric'],
            'motivo' => ['nullable', 'string'],
        ];
    }
}
