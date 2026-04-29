<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Locale;

use Illuminate\Database\Eloquent\Model;
use App\Models\Locale;
use App\MoonShine\Resources\Locale\Pages\LocaleIndexPage;
use App\MoonShine\Resources\Locale\Pages\LocaleFormPage;
use App\MoonShine\Resources\Locale\Pages\LocaleDetailPage;

use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Contracts\Core\PageContract;

/**
 * @extends ModelResource<Locale, LocaleIndexPage, LocaleFormPage, LocaleDetailPage>
 */
class LocaleResource extends ModelResource
{
    protected string $model = Locale::class;

    protected string $title = 'Locales';
    
    protected string $column = 'nombre';
    
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
            \MoonShine\UI\Components\ActionButton::make('Regenerar Token', fn($item) => route('admin.locale.regenerate-token', $item))
                ->icon('arrow-path')
                ->warning()
                ->withConfirm(
                    '¿Generar nuevo Token?', 
                    'Esto desconectará de inmediato cualquier Terminal POS de esta sucursal que use el token actual. Tendrás que reconfigurar las terminales con el nuevo token. ¿Continuar?'
                )
                ->async(\MoonShine\Support\Enums\HttpMethod::POST),

            \MoonShine\UI\Components\ActionButton::make('Descargar Config', fn($item) => route('admin.locale.download-config', $item))
                ->icon('arrow-down-tray')
                ->primary()
                ->blank()

        ];
    }

    public function getTitle(): string
    {
        return __($this->title);
    }
}
