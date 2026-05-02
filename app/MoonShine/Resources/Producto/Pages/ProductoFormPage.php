<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Producto\Pages;

use App\MoonShine\Resources\Producto\ProductoResource;
use Illuminate\Support\Str;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\UI\Collections\Fields;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Flex;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Components\Layout\LineBreak;
use MoonShine\UI\Fields\Image;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends FormPage<ProductoResource>
 */
class ProductoFormPage extends FormPage
{
    protected function fields(): iterable
    {
        return [
            Grid::make([
                Column::make([
                    Box::make(__('Información Principal'), [
                        Text::make(__('Nombre del Producto'), 'nombre')
                            ->required()
                            ->placeholder(__('Ej. Camisa Oxford Azul')),

                        Grid::make([
                            Column::make([
                                Text::make(__('SKU / Código Interno'), 'sku')
                                    ->default(fn () => 'INV-'.now()->format('ymd').'-'.strtoupper(Str::random(4)))
                                    ->hint(__('Auto-generado si se deja vacío')),
                            ])->columnSpan(6),

                            Column::make([
                                // Contenedor optimizado para alineación perfecta del botón con el input
                                Flex::make([
                                    Text::make(__('Código de Barras'), 'codigo_barra')
                                        ->hint(__('Escanear o generar'))
                                        ->customAttributes(['style' => 'flex: 1', 'class' => 'w-full']),

                                    ActionButton::make('', 'javascript:void(0)')
                                        ->onClick(fn () => "const input = \$el.closest('.flex-view')?.querySelector('input[name=\"codigo_barra\"]') || document.getElementsByName('codigo_barra')[0]; if(input) { input.value = '750' + Math.floor(Date.now() / 1000) + Math.floor(Math.random() * 100); }")
                                        ->icon('hashtag')
                                        ->secondary()
                                        ->customAttributes([
                                            'title' => __('Generar Código Random'),
                                            'style' => 'margin-top: 1.8rem;', // Compensa el label para alinear con el input en ambos navegadores
                                        ]),
                                ])->itemsAlign('start')->customAttributes(['class' => 'gap-2 items-start']),
                            ])->columnSpan(6),
                        ]),

                        LineBreak::make(),

                        Textarea::make(__('Descripción Detallada'), 'descripcion')
                            ->placeholder(__('Especificaciones, tallas, material...'))
                            ->customAttributes(['class' => 'w-full']),
                    ])->customAttributes(['class' => 'overflow-hidden']),
                ])->columnSpan(8),

                Column::make([
                    Box::make(__('Multimedia'), [
                        Image::make(__('Imagen Principal'), 'imagen')
                            ->dir('productos/principal')
                            ->disk('public')
                            ->removable()
                            ->customAttributes(['class' => 'max-w-full']),
                        
                        Image::make(__('Galería de Imágenes'), 'galeria')
                            ->dir('productos/galeria')
                            ->disk('public')
                            ->multiple()
                            ->removable()
                            ->customAttributes(['class' => 'max-w-full']),
                    ])->customAttributes(['class' => 'overflow-hidden']),
                ])->columnSpan(4),
            ]),

            LineBreak::make(),

            Box::make(__('Gestión Financiera y Rentabilidad'), [
                Grid::make([
                    Column::make([
                        Text::make(__('Costo (Compra)'), 'precio')
                            ->required()
                            ->customAttributes([
                                'type' => 'text',
                                'inputmode' => 'decimal',
                                'pattern' => '[0-9]*[.,]?[0-9]*',
                                'placeholder' => '0.00',
                                'class' => 'w-full',
                            ])
                            ->hint(__('Precio pagado al proveedor'))
                            ->reactive(function (Fields $fields, ?string $value) {
                                if (is_null($value) || $value === '') {
                                    return $fields;
                                }

                                $cost = (float) str_replace(['$', ' ', ','], ['', '', '.'], $value);
                                $margin = (float) ($fields->onlyFields()->findByColumn('margen')?->getValue() ?? get_global_setting('margen_defecto', 25));

                                $fields->onlyFields()->findByColumn('precio_venta')?->setValue(round($cost * (1 + $margin / 100), 2));

                                return $fields;
                            }, debounce: 400),
                    ])->columnSpan(4),

                    Column::make([
                        Number::make(__('Margen de Ganancia %'), 'margen')
                            ->default(fn () => (float) get_global_setting('margen_defecto', 25))
                            ->step(0.1)
                            ->hint(__('Porcentaje de utilidad'))
                            ->customAttributes(['class' => 'w-full'])
                            ->reactive(function (Fields $fields, ?string $value) {
                                if (is_null($value) || $value === '') {
                                    return $fields;
                                }

                                $cost = (float) str_replace(['$', ' ', ','], ['', '', '.'], $fields->onlyFields()->findByColumn('precio')?->getValue() ?? '0');
                                $margin = (float) $value;

                                $fields->onlyFields()->findByColumn('precio_venta')?->setValue(round($cost * (1 + $margin / 100), 2));

                                return $fields;
                            }, debounce: 400),
                    ])->columnSpan(4),

                    Column::make([
                        Number::make(__('Precio de Venta Sugerido'), 'precio_venta')
                            ->required()
                            ->readonly()
                            ->step(0.01)
                            ->hint(__('Precio final al cliente (Calculado)'))
                            ->customAttributes(['class' => 'bg-slate-50 font-bold text-indigo-600 w-full'])
                            ->reactive(),
                    ])->columnSpan(4),
                ])->customAttributes(['class' => 'gap-4']),
            ]),
        ];
    }
}
