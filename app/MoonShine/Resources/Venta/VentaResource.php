<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Venta;

use Illuminate\Database\Eloquent\Model;
use App\Models\Venta;
use App\MoonShine\Resources\Venta\Pages\VentaIndexPage;
use App\MoonShine\Resources\Venta\Pages\VentaFormPage;
use App\MoonShine\Resources\Venta\Pages\VentaDetailPage;

use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Contracts\Core\PageContract;

/**
 * @extends ModelResource<Venta, VentaIndexPage, VentaFormPage, VentaDetailPage>
 */
class VentaResource extends ModelResource
{
    protected string $model = Venta::class;

    protected string $title = 'Ventas';
    
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
