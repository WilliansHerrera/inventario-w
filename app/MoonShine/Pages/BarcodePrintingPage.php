<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\Models\Producto;
use MoonShine\Laravel\Pages\Page;
use MoonShine\Support\Enums\FormMethod;
use MoonShine\UI\Components\Alert;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\Heading;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Divider;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Select;

class BarcodePrintingPage extends Page
{
    /**
     * @return array<string, string>
     */
    public function getBreadcrumbs(): array
    {
        return [
            '#' => $this->getTitle(),
        ];
    }

    public function getTitle(): string
    {
        return $this->title ?: 'Central de Etiquetado';
    }

    /**
     * @return list<MoonShineComponent>
     */
    public function components(): array
    {
        return [
            Box::make([
                Heading::make('Generación de Viñetas (Códigos de Barras)'),
                Alert::make('information-circle', 'info')
                    ->content('Desde aquí puedes seleccionar múltiples productos para imprimir sus etiquetas de forma masiva.')
                    ->class('mb-4'),

                FormBuilder::make(route('admin.products.barcode'))
                    ->fields([
                        Select::make('Seleccionar Productos', 'ids')
                            ->multiple()
                            ->searchable()
                            ->options(
                                Producto::whereNotNull('codigo_barra')
                                    ->where('codigo_barra', '!=', '')
                                    ->pluck('nombre', 'id')
                                    ->toArray()
                            )
                            ->hint('Solo se muestran productos que ya tienen código de barras asignado')
                            ->required(),

                        Divider::make(),

                        Number::make('Copias por Producto', 'quantity')
                            ->default(1)
                            ->min(1)
                            ->max(100)
                            ->hint('Cantidad de etiquetas que se generarán por cada producto seleccionado'),
                    ])
                    ->submit('Generar Viñetas para Impresión', ['class' => 'btn-primary w-full mt-4'])
                    ->method(FormMethod::GET),
            ]),
        ];
    }
}
