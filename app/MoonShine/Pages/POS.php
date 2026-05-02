<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use MoonShine\Laravel\Pages\Page;
use MoonShine\UI\Components\FlexibleRender;

class POS extends Page
{
    protected ?string $alias = 'p-o-s';

    public function getTitle(): string
    {
        return '';
    }

    public function components(): array
    {
        return [
            FlexibleRender::make(view('admin.pos')),
        ];
    }

    public function render(): string
    {
        return view('admin.pos')->render();
    }
}
