<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use MoonShine\Laravel\Pages\LoginPage as BaseLoginPage;
use MoonShine\Core\Attributes\Layout;
use App\MoonShine\Layouts\CustomLoginLayout;

#[Layout(CustomLoginLayout::class)]
class CustomLoginPage extends BaseLoginPage
{
}
