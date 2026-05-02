<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Producto;

use App\Models\Producto;
use App\MoonShine\Resources\Producto\Pages\ProductoDetailPage;
use App\MoonShine\Resources\Producto\Pages\ProductoFormPage;
use App\MoonShine\Resources\Producto\Pages\ProductoIndexPage;
use Illuminate\Support\Collection;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Components\ActionButton;
use Symfony\Component\HttpFoundation\Response;

/**
 * @extends ModelResource<Producto, ProductoIndexPage, ProductoFormPage, ProductoDetailPage>
 */
class ProductoResource extends ModelResource
{
    protected string $model = Producto::class;

    protected string $title = 'Productos';

    protected string $column = 'nombre';
    
    protected bool $columnSelection = true;

    public function search(): array
    {
        return ['id', 'nombre', 'sku', 'codigo_barra'];
    }

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
                __('Imprimir'),
                fn (Producto $item) => route('admin.products.barcode', $item)
            )
                ->icon('qr-code')
                ->blank()
                ->primary()
                ->customAttributes(['title' => __('Imprimir Código de Barras')]),
        ];
    }

    public function detailButtons(): array
    {
        return [
            ActionButton::make(
                __('Imprimir Código de Barras'),
                fn (Producto $item) => route('admin.products.barcode', $item)
            )
                ->icon('qr-code')
                ->blank()
                ->primary()
                ->customAttributes(['class' => 'btn-lg']),
        ];
    }

    public function actions(): array
    {
        return [
            ActionButton::make(__('Imprimir Seleccionados'))
                ->icon('qr-code')
                ->blank()
                ->primary()
                ->method('bulkPrint')
                ->bulk(),
        ];
    }

    public function bulkPrint(Collection $models): Response
    {
        $ids = $models->pluck('id')->implode(',');

        return response()->redirectTo(route('admin.products.barcode', ['ids' => $ids]));
    }

    public function getTitle(): string
    {
        return __($this->title);
    }
}
