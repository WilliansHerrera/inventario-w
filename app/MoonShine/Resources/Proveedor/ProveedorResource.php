<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Proveedor;

use Illuminate\Database\Eloquent\Model;
use App\Models\Proveedor;
use App\MoonShine\Resources\Proveedor\Pages\ProveedorIndexPage;
use App\MoonShine\Resources\Proveedor\Pages\ProveedorFormPage;
use App\MoonShine\Resources\Proveedor\Pages\ProveedorDetailPage;

use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Contracts\Core\PageContract;

/**
 * @extends ModelResource<Proveedor, ProveedorIndexPage, ProveedorFormPage, ProveedorDetailPage>
 */
class ProveedorResource extends ModelResource
{
    protected string $model = Proveedor::class;

    protected string $title = 'Proveedores';

    protected string $column = 'nombre';
    
    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            ProveedorIndexPage::class,
            ProveedorFormPage::class,
            ProveedorDetailPage::class,
        ];
    }
}
