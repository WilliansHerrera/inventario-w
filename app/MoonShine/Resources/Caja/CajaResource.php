<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Caja;

use App\Models\Caja;
use App\MoonShine\Resources\Caja\Pages\CajaIndexPage;
use App\MoonShine\Resources\Caja\Pages\CajaFormPage;
use App\MoonShine\Resources\Caja\Pages\CajaDetailPage;

use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\UI\Components\ActionButton;

/**
 * @extends ModelResource<Caja, CajaIndexPage, CajaFormPage, CajaDetailPage>
 */
class CajaResource extends ModelResource
{
    protected string $model = Caja::class;

    protected string $title = 'Cajas';
    
    protected string $column = 'nombre';

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            CajaIndexPage::class,
            CajaFormPage::class,
            CajaDetailPage::class,
        ];
    }

    public function rules($item): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'locale_id' => ['required', 'exists:locales,id'],
            'saldo' => ['required', 'numeric'],
        ];
    }

    /**
     * @return list<ActionButton>
     */
    public function buttons(): array
    {
        return [];
    }
}
