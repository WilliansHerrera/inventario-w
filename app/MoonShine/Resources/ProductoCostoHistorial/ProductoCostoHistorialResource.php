<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\ProductoCostoHistorial;

use App\Models\ProductoCostoHistorial;
use App\MoonShine\Resources\ProductoCostoHistorial\Pages\ProductoCostoHistorialDetailPage;
use App\MoonShine\Resources\ProductoCostoHistorial\Pages\ProductoCostoHistorialFormPage;
use App\MoonShine\Resources\ProductoCostoHistorial\Pages\ProductoCostoHistorialIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Enums\Action;
use MoonShine\Support\ListOf;

/**
 * @extends ModelResource<ProductoCostoHistorial, ProductoCostoHistorialIndexPage, ProductoCostoHistorialFormPage, ProductoCostoHistorialDetailPage>
 */
class ProductoCostoHistorialResource extends ModelResource
{
    protected string $model = ProductoCostoHistorial::class;

    protected string $title = 'Historial de Costos';

    protected array $with = ['producto', 'compra', 'user'];

    protected bool $columnSelection = true;

    public function search(): array
    {
        return ['id', 'producto.nombre', 'producto.sku'];
    }

    protected function activeActions(): ListOf
    {
        return new ListOf(Action::class, [Action::VIEW]);
    }

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            ProductoCostoHistorialIndexPage::class,
            ProductoCostoHistorialDetailPage::class,
        ];
    }

    public function getTitle(): string
    {
        return __($this->title);
    }
}
