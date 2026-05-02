<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Caja\Pages;

use App\MoonShine\Resources\Caja\CajaResource;
use App\MoonShine\Resources\CajaMovimientoResource;
use App\MoonShine\Resources\Locale\LocaleResource;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Switcher;
use MoonShine\UI\Fields\Text;

/**
 * @extends DetailPage<CajaResource>
 */
class CajaDetailPage extends DetailPage
{
    protected function fields(): iterable
    {
        return [
            ID::make(),
            Text::make('Nombre'),
            BelongsTo::make('Sucursal', 'sucursal', resource: LocaleResource::class),
            Number::make('Saldo'),
            Switcher::make('Abierta'),

            HasMany::make('Movimientos', 'movimientos', resource: CajaMovimientoResource::class)
                ->fields([
                    ID::make(),
                    Text::make('Tipo', 'tipo')
                        ->badge(fn ($v) => match ($v) {
                            'apertura', 'ingreso' => 'emerald',
                            'cierre' => 'slate',
                            'venta' => 'indigo',
                            'egreso' => 'rose',
                            default => 'gray'
                        }),
                    Number::make('Monto')
                        ->changePreview(fn ($v) => format_currency($v)),
                    Text::make('Descripción', 'descripcion'),
                    Date::make('Fecha', 'created_at')
                        ->format('d/m/Y H:i'),
                ]),
        ];
    }
}
