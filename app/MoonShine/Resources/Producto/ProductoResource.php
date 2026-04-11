<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Producto;

use App\Models\Producto;
use App\MoonShine\Resources\Producto\Pages\ProductoIndexPage;
use App\MoonShine\Resources\Producto\Pages\ProductoFormPage;
use App\MoonShine\Resources\Producto\Pages\ProductoDetailPage;

use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\UI\Components\ActionButton;

/**
 * @extends ModelResource<Producto, ProductoIndexPage, ProductoFormPage, ProductoDetailPage>
 */
class ProductoResource extends ModelResource
{
    protected string $model = Producto::class;

    protected string $title = 'Productos';
    
    protected string $column = 'nombre';

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            ProductoIndexPage::class,
            ProductoFormPage::class,
            ProductoDetailPage::class,
        ];
    }

    public function indexButtons(): array
    {
        return [
            ActionButton::make(
                '',
                fn(Producto $item) => route('admin.products.barcode', $item)
            )
            ->icon('barcode')
            ->blank()
            ->primary()
            ->customAttributes(['title' => 'Imprimir Código de Barras'])
        ];
    }

    public function detailButtons(): array
    {
        return $this->indexButtons();
    }
}
