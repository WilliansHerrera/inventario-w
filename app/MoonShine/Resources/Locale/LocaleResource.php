<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Locale;

use App\Models\Locale;
use App\MoonShine\Resources\Locale\Pages\LocaleDetailPage;
use App\MoonShine\Resources\Locale\Pages\LocaleFormPage;
use App\MoonShine\Resources\Locale\Pages\LocaleIndexPage;
use MoonShine\Contracts\Core\PageContract;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\Enums\HttpMethod;
use MoonShine\UI\Components\ActionButton;

/**
 * @extends ModelResource<Locale, LocaleIndexPage, LocaleFormPage, LocaleDetailPage>
 */
class LocaleResource extends ModelResource
{
    protected string $model = Locale::class;

    protected string $title = 'Locales';

    protected string $column = 'nombre';
    
    protected bool $columnSelection = true;

    public function search(): array
    {
        return ['id', 'nombre', 'direccion'];
    }

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            LocaleIndexPage::class,
            LocaleFormPage::class,
            LocaleDetailPage::class,
        ];
    }

    protected function indexButtons(): array
    {
        return [
            ActionButton::make(__('Regenerar Token'), fn ($item) => route('admin.locale.regenerate-token', $item))
                ->icon('arrow-path')
                ->warning()
                ->withConfirm(
                    __('¿Generar nuevo Token?'),
                    __('Esto desconectará de inmediato cualquier Terminal POS de esta sucursal que use el token actual. Tendrás que reconfigurar las terminales con el nuevo token. ¿Continuar?')
                )
                ->async(HttpMethod::POST),

            ActionButton::make(__('Descargar Config'), fn ($item) => route('admin.locale.download-config', $item))
                ->icon('arrow-down-tray')
                ->primary()
                ->blank(),

        ];
    }

    public function getTitle(): string
    {
        return __($this->title);
    }
}
