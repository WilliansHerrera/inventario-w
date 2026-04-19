<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Caja\Pages;

use MoonShine\Laravel\Pages\Crud\DetailPage;
use App\MoonShine\Resources\Caja\CajaResource;

/**
 * @extends DetailPage<CajaResource>
 */
class CajaDetailPage extends DetailPage
{
    protected function fields(): iterable
    {
        return [
            \MoonShine\UI\Fields\ID::make(),
            \MoonShine\UI\Fields\Text::make('Nombre'),
            \MoonShine\Laravel\Fields\Relationships\BelongsTo::make('Sucursal', 'sucursal', resource: \App\MoonShine\Resources\Locale\LocaleResource::class),
            \MoonShine\UI\Fields\Number::make('Saldo'),
            \MoonShine\UI\Fields\Switcher::make('Abierta'),

            \MoonShine\Laravel\Fields\Relationships\HasMany::make('Movimientos', 'movimientos', resource: \App\MoonShine\Resources\CajaMovimientoResource::class)
                ->fields([
                    \MoonShine\UI\Fields\ID::make(),
                    \MoonShine\UI\Fields\Text::make('Tipo', 'tipo')
                        ->badge(fn($v) => match($v) {
                            'apertura', 'ingreso' => 'emerald',
                            'cierre' => 'slate',
                            'venta' => 'indigo',
                            'egreso' => 'rose',
                            default => 'gray'
                        }),
                    \MoonShine\UI\Fields\Number::make('Monto')
                        ->changePreview(fn($v) => format_currency($v)),
                    \MoonShine\UI\Fields\Text::make('Descripción', 'descripcion'),
                    \MoonShine\UI\Fields\Date::make('Fecha', 'created_at')
                        ->format('d/m/Y H:i'),
                ])
        ];
    }
}
