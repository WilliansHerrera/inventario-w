<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Venta\Pages;

use App\MoonShine\Resources\Caja\CajaResource;
use App\MoonShine\Resources\MoonShineUser\MoonShineUserResource;
use App\MoonShine\Resources\Producto\ProductoResource;
use App\MoonShine\Resources\Venta\VentaResource;
use App\MoonShine\Resources\VentaDetalle\VentaDetalleResource;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\FormBuilderContract;
use MoonShine\Laravel\Fields\Relationships\BelongsTo;
use MoonShine\Laravel\Fields\Relationships\HasMany;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Text;
use Throwable;

/**
 * @extends FormPage<VentaResource>
 */
class VentaFormPage extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            BelongsTo::make('Caja', 'caja', resource: CajaResource::class)
                ->required(),
            BelongsTo::make('Vendedor', 'user', resource: MoonShineUserResource::class)
                ->default(auth()->id())
                ->required(),
            Text::make('Método de Pago', 'metodo_pago')
                ->default('efectivo')
                ->required(),
            Number::make('Total', 'total')
                ->readonly()
                ->default(0)
                ->hint(get_currency_symbol().' '.get_global_setting('currency_code')),

            HasMany::make('Productos', 'detalles', resource: VentaDetalleResource::class)
                ->fields([
                    BelongsTo::make('Producto', 'producto', resource: ProductoResource::class)
                        ->searchable(),
                    Number::make('Cantidad', 'cantidad'),
                    Number::make('Precio Unitario', 'precio_unitario')
                        ->hint(get_currency_symbol().' '.get_global_setting('currency_code')),
                    Number::make('Subtotal', 'subtotal')
                        ->hint(get_currency_symbol().' '.get_global_setting('currency_code')),
                ])
                ->creatable(),
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
