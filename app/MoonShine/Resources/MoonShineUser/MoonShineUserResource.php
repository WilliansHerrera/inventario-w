<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\MoonShineUser;

use App\Models\User;
use App\MoonShine\Resources\MoonShineUser\Pages\MoonShineUserFormPage;
use App\MoonShine\Resources\MoonShineUser\Pages\MoonShineUserIndexPage;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\MenuManager\Attributes\Group;
use MoonShine\MenuManager\Attributes\Order;
use MoonShine\Support\Attributes\Icon;
use MoonShine\Support\Enums\Action;
use MoonShine\Support\ListOf;

/**
 * @extends ModelResource<User, MoonShineUserIndexPage, MoonShineUserFormPage, null>
 */
#[Icon('users')]
#[Group('moonshine::ui.resource.system', 'users', translatable: true)]
#[Order(0)]
class MoonShineUserResource extends ModelResource
{
    protected string $model = User::class;

    protected string $column = 'name';

    protected bool $columnSelection = true;

    protected array $with = ['moonshineUserRole'];

    public function search(): array
    {
        return ['id', 'name', 'email'];
    }

    public function getTitle(): string
    {
        return __('moonshine::ui.resource.admins_title');
    }

    protected function activeActions(): ListOf
    {
        return parent::activeActions()->except(Action::VIEW);
    }

    protected function pages(): array
    {
        return [
            MoonShineUserIndexPage::class,
            MoonShineUserFormPage::class,
        ];
    }
}
