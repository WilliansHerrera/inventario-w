<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\CajaTurno;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Enums\PageType;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Relationships\BelongsTo;
use MoonShine\UI\Components\Badge;

class CajaTurnoResource extends ModelResource
{
    protected string $model = CajaTurno::class;

    protected string $title = 'Historial de Turnos / Arqueos';

    protected function activeActions(): \MoonShine\Support\ListOf
    {
        return parent::activeActions()->except(\MoonShine\Support\Enums\Action::CREATE, \MoonShine\Support\Enums\Action::UPDATE, \MoonShine\Support\Enums\Action::DELETE);
    }

    public function fields(): array
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('Caja', 'caja', resource: CajaResource::class),
            BelongsTo::make('Usuario', 'user', resource: \App\MoonShine\Resources\MoonShineUserResource::class),
            
            \MoonShine\UI\Fields\Layout\Box::make('Auditoría de Apertura', [
                Number::make('Esp. Apertura', 'monto_apertura_esperado')->badge('gray'),
                Number::make('Real Apertura', 'monto_apertura_real')->badge('info'),
                Number::make('Dif. Apertura', 'diferencia_apertura')
                    ->badge(fn($item) => $item->diferencia_apertura < 0 ? 'error' : ($item->diferencia_apertura > 0 ? 'warning' : 'success')),
            ]),

            \MoonShine\UI\Fields\Layout\Box::make('Auditoría de Cierre', [
                Number::make('Esp. Cierre', 'monto_cierre_esperado')->badge('gray'),
                Number::make('Real Cierre', 'monto_cierre_real')->badge('info'),
                Number::make('Dif. Cierre', 'diferencia')
                    ->badge(fn($item) => $item->diferencia < 0 ? 'error' : ($item->diferencia > 0 ? 'warning' : 'success')),
            ]),

            Date::make('Abierto', 'abierto_at')->format('d/m/Y H:i'),
            Date::make('Cerrado', 'cerrado_at')->format('d/m/Y H:i'),
            
            Text::make('Estado', 'estado', fn($item) => Badge::make($item->estado, $item->estado === 'abierto' ? 'success' : 'gray'))
        ];
    }

    public function rules($item): array
    {
        return [];
    }
}
