<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Producto\Pages;

use MoonShine\Laravel\Pages\Crud\DetailPage;
use App\MoonShine\Resources\Producto\ProductoResource;

/**
 * @extends DetailPage<ProductoResource>
 */
class ProductoDetailPage extends DetailPage
{
    protected function fields(): iterable
    {
        return [];
    }
}
