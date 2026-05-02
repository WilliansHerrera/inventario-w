<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\VentaDetalle\Pages;

use App\MoonShine\Resources\Producto\ProductoResource;
use App\MoonShine\Resources\VentaDetalle\VentaDetalleResource;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\FormBuilderContract;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Fields\Number;
use Throwable;

/**
 * @extends FormPage<VentaDetalleResource>
 */
class VentaDetalleFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            BelongsTo::make('Producto', 'producto', resource: ProductoResource::class)
                ->searchable()
                ->required(),
            Number::make('Cantidad', 'cantidad')->required(),
            Number::make('Precio Unitario', 'precio_unitario')
                ->required()
                ->hint(get_currency_symbol().' '.get_global_setting('currency_code')),
            Number::make('Subtotal', 'subtotal')
                ->required()
                ->hint(get_currency_symbol().' '.get_global_setting('currency_code')),
        ];
    }

    protected function buttons(): ListOf
    {
        return parent::buttons();
    }

    protected function formButtons(): ListOf
    {
        return parent::formButtons();
    }

    protected function rules(DataWrapperContract $item): array
    {
        return [];
    }

    /**
     * @param  FormBuilder  $component
     * @return FormBuilder
     */
    protected function modifyFormComponent(FormBuilderContract $component): FormBuilderContract
    {
        return $component;
    }

    /**
     * @return list<ComponentContract>
     *
     * @throws Throwable
     */
    protected function topLayer(): array
    {
        return [
            ...parent::topLayer(),
        ];
    }

    /**
     * @return list<ComponentContract>
     *
     * @throws Throwable
     */
    protected function mainLayer(): array
    {
        return [
            ...parent::mainLayer(),
        ];
    }

    /**
     * @return list<ComponentContract>
     *
     * @throws Throwable
     */
    protected function bottomLayer(): array
    {
        return [
            ...parent::bottomLayer(),
        ];
    }
}
