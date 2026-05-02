<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Compra;

use App\Models\Compra;
use App\MoonShine\Resources\Compra\Pages\CompraDetailPage;
use App\MoonShine\Resources\Compra\Pages\CompraFormPage;
use App\MoonShine\Resources\Compra\Pages\CompraIndexPage;
use App\Services\CompraService;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\ActionButtonContract;
use MoonShine\Laravel\MoonShineAuth;
use MoonShine\Laravel\MoonShineUI;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Attributes\AsyncMethod;
use Symfony\Component\HttpFoundation\Response;

/**
 * @extends ModelResource<Compra, CompraIndexPage, CompraFormPage, CompraDetailPage>
 */
class CompraResource extends ModelResource
{
    protected string $model = Compra::class;

    protected string $title = 'Recepción de Compras';

    protected string $column = 'nro_documento';
    
    protected bool $columnSelection = true;

    public function search(): array
    {
        return ['id', 'nro_documento', 'proveedor.nombre'];
    }

    protected array $with = ['proveedor', 'locale'];

    public function getRedirectAfterSave(): ?string
    {
        return $this->getFormPageUrl($this->getItem()?->getKey());
    }

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            CompraIndexPage::class,
            CompraFormPage::class,
            CompraDetailPage::class,
        ];
    }

    public function buttons(): ListOf
    {
        return new ListOf(ActionButtonContract::class, []);
    }

    #[AsyncMethod]
    public function completarCompra(): Response
    {
        $item = $this->getItem();

        if ($item === null) {
            MoonShineUI::toast(__('Error: Registro no encontrado.'), 'error');

            return back();
        }

        try {
            (new CompraService)->processPurchase($item);

            MoonShineUI::toast(__('Compra procesada exitosamente. El inventario ha sido actualizado.'), 'success');
        } catch (\Throwable $e) {
            MoonShineUI::toast(__('Error: ') . $e->getMessage(), 'error');
        }

        return back();
    }

    protected function beforeCreating(DataWrapperContract $item): DataWrapperContract
    {
        $model = $item->getOriginal();
        $model->user_id = MoonShineAuth::getGuard()->id();
        $model->estado = 'borrador';

        return $item;
    }

    public function getTitle(): string
    {
        return __($this->title);
    }
}
