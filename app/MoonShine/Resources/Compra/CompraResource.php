<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Compra;

use Illuminate\Database\Eloquent\Model;
use App\Models\Compra;
use App\MoonShine\Resources\Compra\Pages\CompraIndexPage;
use App\MoonShine\Resources\Compra\Pages\CompraFormPage;
use App\MoonShine\Resources\Compra\Pages\CompraDetailPage;

use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\Core\DependencyInjection\FieldsContract;
use MoonShine\Support\Attributes\AsyncMethod;
use MoonShine\Laravel\MoonShineAuth;

/**
 * @extends ModelResource<Compra, CompraIndexPage, CompraFormPage, CompraDetailPage>
 */
class CompraResource extends ModelResource
{
    protected string $model = Compra::class;

    protected string $title = 'Recepción de Compras';

    protected string $column = 'nro_documento';

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
        return new ListOf(\MoonShine\Contracts\UI\ActionButtonContract::class, []);
    }

    #[AsyncMethod]
    public function completarCompra(): \Symfony\Component\HttpFoundation\Response
    {
        $item = $this->getItem();

        if ($item === null) {
             \MoonShine\Laravel\MoonShineUI::toast('Error: Registro no encontrado.', 'error');
             return back();
        }

        try {
            (new \App\Services\CompraService())->procesarCompra($item);
            
            \MoonShine\Laravel\MoonShineUI::toast('Compra procesada exitosamente. El inventario ha sido actualizado.', 'success');
        } catch (\Throwable $e) {
            \MoonShine\Laravel\MoonShineUI::toast('Error: ' . $e->getMessage(), 'error');
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
