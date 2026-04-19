<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\ProductoCostoHistorial;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProductoCostoHistorial;
use App\MoonShine\Resources\ProductoCostoHistorial\Pages\ProductoCostoHistorialIndexPage;
use App\MoonShine\Resources\ProductoCostoHistorial\Pages\ProductoCostoHistorialFormPage;
use App\MoonShine\Resources\ProductoCostoHistorial\Pages\ProductoCostoHistorialDetailPage;

use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Support\ListOf;
use MoonShine\Support\Enums\Action;

/**
 * @extends ModelResource<ProductoCostoHistorial, ProductoCostoHistorialIndexPage, ProductoCostoHistorialFormPage, ProductoCostoHistorialDetailPage>
 */
class ProductoCostoHistorialResource extends ModelResource
{
    protected string $model = ProductoCostoHistorial::class;

    protected string $title = 'Historial de Costos';

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
}
