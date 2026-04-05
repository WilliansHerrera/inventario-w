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
}
