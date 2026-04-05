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
            \MoonShine\UI\Components\Layout\Box::make('Información General', [
                \MoonShine\UI\Fields\ID::make(),
                \MoonShine\UI\Fields\Text::make('Nombre'),
                \MoonShine\Laravel\Fields\Relationships\BelongsTo::make('Sucursal', 'sucursal'),
                \MoonShine\UI\Fields\Number::make('Saldo'),
                \MoonShine\UI\Fields\Switcher::make('Abierta'),
            ]),

            \MoonShine\UI\Components\Layout\Divider::make(),

            \MoonShine\UI\Components\Layout\Box::make('Historial de Movimientos (Auditoría)', [
                \MoonShine\Laravel\Fields\Relationships\HasMany::make('Movimientos', 'movimientos')
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
                            ->format(fn($v) => '$ ' . number_format((float)$v, 2)),
                        \MoonShine\UI\Fields\Text::make('Descripción', 'descripcion'),
                        \MoonShine\UI\Fields\Date::make('Fecha', 'created_at')
                            ->format('d/m/Y H:i'),
                    ])
            ])
        ];
    }
}
