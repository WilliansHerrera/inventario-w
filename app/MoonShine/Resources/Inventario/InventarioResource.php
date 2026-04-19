<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Inventario;

use App\Models\Inventario;
use App\MoonShine\Resources\Inventario\Pages\InventarioIndexPage;
use App\MoonShine\Resources\Inventario\Pages\InventarioFormPage;
use App\MoonShine\Resources\Inventario\Pages\InventarioDetailPage;

use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\UI\Components\ActionButton;
use Illuminate\Validation\Rule;

/**
 * @extends ModelResource<Inventario, InventarioIndexPage, InventarioFormPage, InventarioDetailPage>
 */
class InventarioResource extends ModelResource
{
    protected string $model = Inventario::class;

    protected string $title = 'Inventarios';

    protected string $column = 'id';

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            InventarioIndexPage::class,
            InventarioFormPage::class,
            InventarioDetailPage::class,
        ];
    }

    public function rules($item): array
    {
        return [
            'producto_id' => [
                'required',
                Rule::unique('inventarios', 'producto_id')
                    ->where('locale_id', request()->input('locale_id'))
                    ->ignore($item->id),
            ],
            'locale_id' => ['required'],
            'stock' => ['required', 'integer', 'min:0'],
        ];
    }

    public function indexButtons(): array
    {
        return [
            ActionButton::make(
                'Imprimir',
                fn(Inventario $item) => route('admin.products.barcode', $item->producto_id)
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
                fn(Inventario $item) => route('admin.products.barcode', $item->producto_id)
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
        $ids = $models->pluck('producto_id')->unique()->implode(',');
        
        return response()->redirectTo(route('admin.products.barcode', ['ids' => $ids]));
    }
}
