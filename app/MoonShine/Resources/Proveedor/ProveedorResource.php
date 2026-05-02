<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Proveedor;

use App\Models\Proveedor;
use App\MoonShine\Resources\Proveedor\Pages\ProveedorDetailPage;
use App\MoonShine\Resources\Proveedor\Pages\ProveedorFormPage;
use App\MoonShine\Resources\Proveedor\Pages\ProveedorIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;

/**
 * @extends ModelResource<Proveedor, ProveedorIndexPage, ProveedorFormPage, ProveedorDetailPage>
 */
class ProveedorResource extends ModelResource
{
    protected string $model = Proveedor::class;

    protected string $title = 'Proveedores';

    protected bool $columnSelection = true;

    public function search(): array
    {
        return ['id', 'nombre', 'email', 'telefono'];
    }

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

    public function getTitle(): string
    {
        return __($this->title);
    }
}
