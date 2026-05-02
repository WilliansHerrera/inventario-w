<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use MoonShine\Laravel\Pages\Page;
use MoonShine\UI\Components\FlexibleRender;

class BackupPage extends Page
{
    public function getTitle(): string
    {
        return 'Copias de Seguridad';
    }

    public function components(): array
    {
        return [
            FlexibleRender::make(view('admin.backups')),
        ];
    }
}
