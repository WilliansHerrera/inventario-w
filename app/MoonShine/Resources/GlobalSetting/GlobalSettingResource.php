<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\GlobalSetting;

use App\Models\GlobalSetting;
use MoonShine\Contracts\Core\DependencyInjection\FieldsContract;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Contracts\Core\TypeCasts\DataWrapperContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Laravel\MoonShineUI;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Enums\Action;
use MoonShine\Support\Enums\ToastType;
use MoonShine\Support\ListOf;

/**
 * @extends ModelResource<GlobalSetting>
 */
class GlobalSettingResource extends ModelResource
{
    protected string $model = GlobalSetting::class;

    protected string $title = 'Configuración Regional';
    
    protected bool $isAsync = false;

    protected function activeActions(): ListOf
    {
        return parent::activeActions()->except(Action::CREATE, Action::DELETE, Action::MASS_DELETE);
    }

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            Pages\GlobalSettingIndexPage::class,
            Pages\GlobalSettingFormPage::class,
        ];
    }

    /**
     * @return list<FieldContract>
     */
    protected function fields(): array
    {
        return [];
    }

    public function getRedirectAfterSave(): ?string
    {
        return $this->getIndexPageUrl();
    }

    protected function afterSave(DataWrapperContract $item, FieldsContract $fields): DataWrapperContract
    {
        \Illuminate\Support\Facades\Cache::forget('global_settings_v2');
        
        $key = config('moonshine.locale_key', '_lang');
        session()->forget($key);

        MoonShineUI::toast('Configuración guardada exitosamente.', ToastType::SUCCESS);

        return $item;
    }

    protected function rules(mixed $item): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return __($this->title);
    }
}
