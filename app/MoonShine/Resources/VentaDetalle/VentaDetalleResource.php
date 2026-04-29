<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\VentaDetalle;

use Illuminate\Database\Eloquent\Model;
use App\Models\VentaDetalle;
use App\MoonShine\Resources\VentaDetalle\Pages\VentaDetalleIndexPage;
use App\MoonShine\Resources\VentaDetalle\Pages\VentaDetalleFormPage;
use App\MoonShine\Resources\VentaDetalle\Pages\VentaDetalleDetailPage;

use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Contracts\Core\PageContract;

/**
 * @extends ModelResource<VentaDetalle, VentaDetalleIndexPage, VentaDetalleFormPage, VentaDetalleDetailPage>
 */
class VentaDetalleResource extends ModelResource
{
    protected string $model = VentaDetalle::class;

    protected string $title = 'Detalles de Venta';

    protected string $column = 'id';
    
    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            VentaDetalleIndexPage::class,
            VentaDetalleFormPage::class,
            VentaDetalleDetailPage::class,
        ];
    }

    public function getTitle(): string
    {
        return __($this->title);
    }
}
