<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use MoonShine\Laravel\Pages\Page;
use MoonShine\UI\Components\FlexibleRender;

class BackupPage extends Page
{
    /**
     * @return string
     */
    public function getTitle(): string
    {
        return 'Copias de Seguridad';
    }

    /**
     * @return array
     */
    public function components(): array
    {
        return [
            FlexibleRender::make(view('admin.backups')),
        ];
    }
}
