<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\CajaTurno;

use App\Models\CajaTurno;
use App\MoonShine\Resources\CajaTurno\Pages\CajaTurnoDetailPage;
use App\MoonShine\Resources\CajaTurno\Pages\CajaTurnoFormPage;
use App\MoonShine\Resources\CajaTurno\Pages\CajaTurnoIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Enums\Action;
use MoonShine\Support\ListOf;

/**
 * @extends ModelResource<CajaTurno, CajaTurnoIndexPage, CajaTurnoFormPage, CajaTurnoDetailPage>
 */
class CajaTurnoResource extends ModelResource
{
    protected string $model = CajaTurno::class;

    protected string $title = 'Historial de Turnos / Arqueos';

    protected function activeActions(): ListOf
    {
        return parent::activeActions()->except(Action::CREATE, Action::UPDATE, Action::DELETE);
    }

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            CajaTurnoIndexPage::class,
            CajaTurnoFormPage::class,
            CajaTurnoDetailPage::class,
        ];
    }

    public function search(): array
    {
        return [
            'id',
            'estado',
        ];
    }

    public function rules($item): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return __($this->title);
    }
}
