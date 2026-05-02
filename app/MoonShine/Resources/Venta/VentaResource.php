<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Venta;

use App\Models\Venta;
use App\MoonShine\Resources\Venta\Pages\VentaDetailPage;
use App\MoonShine\Resources\Venta\Pages\VentaFormPage;
use App\MoonShine\Resources\Venta\Pages\VentaIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;

/**
 * @extends ModelResource<Venta, VentaIndexPage, VentaFormPage, VentaDetailPage>
 */
class VentaResource extends ModelResource
{
    protected string $model = Venta::class;

    protected string $title = 'Ventas';

    protected bool $columnSelection = true;

    protected array $with = ['user', 'caja'];

    public function search(): array
    {
        return ['id', 'total', 'user.name', 'caja.nombre'];
    }

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            VentaIndexPage::class,
            VentaFormPage::class,
            VentaDetailPage::class,
        ];
    }

    public function getTitle(): string
    {
        return __($this->title);
    }
}
