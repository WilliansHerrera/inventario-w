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
                'Imprimir',
                fn(Producto $item) => route('admin.products.barcode', $item)
            )
            ->icon('qr-code')
            ->blank()
            ->primary()
            ->customAttributes(['title' => 'Imprimir Código de Barras'])
        ];
    }

    public function detailButtons(): array
    {
        return [
            ActionButton::make(
                'Imprimir Código de Barras',
                fn(Producto $item) => route('admin.products.barcode', $item)
            )
            ->icon('qr-code')
            ->blank()
            ->primary()
            ->customAttributes(['class' => 'btn-lg'])
        ];
    }

    public function actions(): array
    {
        return [
            ActionButton::make('Imprimir Seleccionados')
                ->icon('qr-code')
                ->blank()
                ->primary()
                ->method('bulkPrint')
                ->bulk()
        ];
    }

    public function bulkPrint(\Illuminate\Support\Collection $models): \Symfony\Component\HttpFoundation\Response
    {
        $ids = $models->pluck('id')->implode(',');
        
        return response()->redirectTo(route('admin.products.barcode', ['ids' => $ids]));
    }
}
