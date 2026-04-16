<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\CajaTurno\Pages;

use MoonShine\Laravel\Pages\Crud\IndexPage;
use App\MoonShine\Resources\Caja\CajaResource;
use App\MoonShine\Resources\MoonShineUser\MoonShineUserResource;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Date;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\UI\Components\Badge;
use MoonShine\Contracts\UI\FieldContract;

class CajaTurnoIndexPage extends IndexPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('Caja', 'caja', resource: CajaResource::class)->sortable(),
            BelongsTo::make('Cajero', 'user', resource: MoonShineUserResource::class),
            
            Number::make('Real Apertura', 'monto_apertura_real')
                ->changePreview(fn($v) => format_currency((float)$v)),
            Number::make('Dif. Apertura', 'diferencia_apertura')
                ->changePreview(fn($v) => Badge::make(
                    format_currency((float)$v), 
                    (float)$v < 0 ? 'error' : ((float)$v > 0 ? 'warning' : 'success')
                )),
                
            Number::make('Real Cierre', 'monto_cierre_real')
                ->changePreview(fn($v) => format_currency((float)$v)),
            Number::make('Dif. Cierre', 'diferencia')
                ->changePreview(fn($v) => Badge::make(
                    format_currency((float)$v), 
                    (float)$v < 0 ? 'error' : ((float)$v > 0 ? 'warning' : 'success')
                )),

            Date::make('Abierto', 'abierto_at')->format('d/M H:i')->sortable(),
            Date::make('Cerrado', 'cerrado_at')->format('d/M H:i')->sortable(),
            
            Text::make('Estado', 'estado')->changePreview(fn($v) => Badge::make($v, $v === 'abierto' ? 'success' : 'gray')),
        ];
    }
}
