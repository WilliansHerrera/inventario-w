<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\CajaMovimiento;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;

/**
 * @extends ModelResource<CajaMovimiento>
 */
class CajaMovimientoResource extends ModelResource
{
    protected string $model = CajaMovimiento::class;

    protected string $title = 'Movimientos de Caja';

    public function search(): array
    {
        return ['id', 'tipo', 'descripcion'];
    }

    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            Text::make('Tipo', 'tipo'),
            Number::make('Monto', 'monto'),
            Text::make('Descripción', 'descripcion'),
            Date::make('Fecha', 'created_at'),
        ];
    }

    public function rules($item): array
    {
        return [
            'tipo' => ['required'],
            'monto' => ['required', 'numeric'],
        ];
    }

    public function getTitle(): string
    {
        return __($this->title);
    }
}
