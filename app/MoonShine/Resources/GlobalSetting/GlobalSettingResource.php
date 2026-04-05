<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\GlobalSetting;

use App\Models\GlobalSetting;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Support\ListOf;
use MoonShine\Support\Enums\Action;
use MoonShine\ColorManager\Palettes\PurplePalette;
use MoonShine\ColorManager\Palettes\CyanPalette;
use MoonShine\ColorManager\Palettes\GreenPalette;
use MoonShine\ColorManager\Palettes\YellowPalette;
use MoonShine\ColorManager\Palettes\OrangePalette;
use MoonShine\ColorManager\Palettes\PinkPalette;
use MoonShine\ColorManager\Palettes\RosePalette;
use MoonShine\ColorManager\Palettes\SkyPalette;
use MoonShine\ColorManager\Palettes\TealPalette;
use MoonShine\ColorManager\Palettes\GrayPalette;
use MoonShine\ColorManager\Palettes\NeutralPalette;
use MoonShine\ColorManager\Palettes\LimePalette;
use MoonShine\ColorManager\Palettes\HalloweenPalette;
use MoonShine\ColorManager\Palettes\RetroPalette;
use MoonShine\ColorManager\Palettes\SpringPalette;
use MoonShine\ColorManager\Palettes\ValentinePalette;
use MoonShine\ColorManager\Palettes\WinterPalette;

/**
 * @extends ModelResource<GlobalSetting>
 */
class GlobalSettingResource extends ModelResource
{
    protected string $model = GlobalSetting::class;

    protected string $title = 'Configuración Regional';

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

    protected function rules(mixed $item): array
    {
        return [];
    }
}
