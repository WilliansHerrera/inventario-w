<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\CajaTurno\Pages;

use App\MoonShine\Resources\Caja\CajaResource;
use App\MoonShine\Resources\MoonShineUser\MoonShineUserResource;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Crud\Collections\Fields;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\UI\Components\Badge;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Flex;
use MoonShine\UI\Components\Layout\LineBreak;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Json;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;

class CajaTurnoDetailPage extends DetailPage
{
    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('Caja', 'caja', resource: CajaResource::class),
            BelongsTo::make('Cajero', 'user', resource: MoonShineUserResource::class),
            Date::make('Apertura', 'abierto_at')->format('d/m/Y H:i:s'),
            Date::make('Cierre', 'cerrado_at')->format('d/m/Y H:i:s'),
            Text::make('Estado', 'estado'),

            Number::make('Monto Apertura Esperado', 'monto_apertura_esperado'),
            Number::make('Monto Apertura Real', 'monto_apertura_real'),
            Number::make('Diferencia Apertura', 'diferencia_apertura'),

            Number::make('Monto Cierre Esperado', 'monto_cierre_esperado'),
            Number::make('Monto Cierre Real', 'monto_cierre_real'),
            Number::make('Diferencia Final (Cierre)', 'diferencia'),

            Json::make('Denominaciones en Apertura', 'denominaciones_apertura'),
            Json::make('Denominaciones en Cierre', 'denominaciones_cierre'),
        ];
    }

    protected function mainLayer(): array
    {
        $resource = $this->getResource();
        $item = $resource->getCastedData();

        return [
            Box::make('Información General', [
                TableBuilder::make(Fields::make([
                    ID::make()->sortable(),
                    BelongsTo::make('Caja', 'caja', resource: CajaResource::class),
                    BelongsTo::make('Cajero', 'user', resource: MoonShineUserResource::class),
                    Date::make('Apertura', 'abierto_at')->format('d/m/Y H:i:s'),
                    Date::make('Cierre', 'cerrado_at')->format('d/m/Y H:i:s'),
                    Text::make('Estado', 'estado')->changePreview(fn ($v) => Badge::make((string) $v, $v === 'abierto' ? 'success' : 'gray')),
                ]))
                    ->cast($resource->getCaster())
                    ->items([$item])
                    ->vertical()
                    ->simple()
                    ->preview(),
            ]),

            Box::make('Auditoría Financiera', [
                TableBuilder::make(Fields::make([
                    Number::make('Monto Apertura Esperado', 'monto_apertura_esperado')->changePreview(fn ($v) => format_currency((float) $v)),
                    Number::make('Monto Apertura Real', 'monto_apertura_real')->changePreview(fn ($v) => format_currency((float) $v)),
                    Number::make('Diferencia Apertura', 'diferencia_apertura')->changePreview(fn ($v) => format_currency((float) $v)),

                    Number::make('Monto Cierre Esperado', 'monto_cierre_esperado')->changePreview(fn ($v) => format_currency((float) $v)),
                    Number::make('Monto Cierre Real', 'monto_cierre_real')->changePreview(fn ($v) => format_currency((float) $v)),
                    Number::make('Diferencia Final (Cierre)', 'diferencia')->changePreview(fn ($v) => format_currency((float) $v)),
                ]))
                    ->cast($resource->getCaster())
                    ->items([$item])
                    ->vertical()
                    ->simple()
                    ->preview(),
            ]),

            Box::make('Desglose de Efectivo', [
                Flex::make([
                    TableBuilder::make(Fields::make([
                        Json::make('Denominaciones en Apertura', 'denominaciones_apertura')->keyValue(),
                    ]))
                        ->cast($resource->getCaster())
                        ->items([$item])
                        ->vertical()
                        ->simple()
                        ->preview(),

                    TableBuilder::make(Fields::make([
                        Json::make('Denominaciones en Cierre', 'denominaciones_cierre')->keyValue(),
                    ]))
                        ->cast($resource->getCaster())
                        ->items([$item])
                        ->vertical()
                        ->simple()
                        ->preview(),
                ]),

                LineBreak::make(),

                ...$this->getTopButtons(),
            ]),
        ];
    }
}
