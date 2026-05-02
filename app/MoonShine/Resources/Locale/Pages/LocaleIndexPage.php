<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Locale\Pages;

use App\MoonShine\Resources\Locale\LocaleResource;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Laravel\QueryTags\QueryTag;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\Alert;
use MoonShine\UI\Components\Badge;
use MoonShine\UI\Components\Metrics\Wrapped\Metric;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\UI\Fields\ID;
use MoonShine\UI\Fields\Text;
use Throwable;

/**
 * @extends IndexPage<LocaleResource>
 */
class LocaleIndexPage extends IndexPage
{
    protected bool $isLazy = true;

    /**
     * @return list<FieldContract>
     */
    protected function fields(): iterable
    {
        return [
            ID::make()->sortable()->columnSelection(false),
            Text::make('Nombre', 'nombre')->sortable()->required()->columnSelection(false),
            Text::make('Dirección', 'direccion'),
            Text::make('Teléfono', 'telefono'),
            Text::make('Token POS (Sucursal)', 'sync_token')
                ->changePreview(fn ($value) => Badge::make($value ?? '', 'primary')
                    ->customAttributes([
                        'x-data' => '{}',
                        'class' => 'cursor-pointer',
                        '@click' => "window.navigator.clipboard.writeText('{$value}'); \$dispatch('toast', {type: 'success', text: 'Token Copiado!'})",
                    ])
                ),
            Text::make('Configuración JSON', 'id')
                ->changePreview(function ($value, Text $ctx) {
                    $item = $ctx->getData();
                    if ($item instanceof DataWrapperContract) {
                        $item = $item->getOriginal();
                    }
                    $jsonContent = base64_encode($item->getConfigJson());

                    return Badge::make('Copiar Configuración', 'success')
                        ->customAttributes([
                            'x-data' => '{}',
                            'class' => 'cursor-pointer',
                            '@click' => "window.navigator.clipboard.writeText('{$jsonContent}'); \$dispatch('toast', {type: 'success', text: '¡Configuración de Sucursal Copiada!'})",
                        ]);
                }),
        ];
    }

    protected function buttons(): ListOf
    {
        return parent::buttons();
    }

    /**
     * @return list<FieldContract>
     */
    protected function filters(): iterable
    {
        return [];
    }

    /**
     * @return list<QueryTag>
     */
    protected function queryTags(): array
    {
        return [];
    }

    /**
     * @return list<Metric>
     */
    protected function metrics(): array
    {
        return [];
    }

    /**
     * @param  TableBuilder  $component
     * @return TableBuilder
     */
    protected function modifyListComponent(ComponentContract $component): ComponentContract
    {
        return $component->columnSelection();
    }

    /**
     * @return list<ComponentContract>
     *
     * @throws Throwable
     */
    protected function topLayer(): array
    {
        return [
            Alert::make(
                icon: 'computer-desktop',
                type: 'primary',
            )->content('Para instalar terminales POS, descarga "POS-Setup.exe" y usa "Copiar Configuración" para cada sucursal respectiva.'),
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
