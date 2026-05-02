<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\MoonShine\Layouts\CustomLoginLayout;
use MoonShine\Core\Attributes\Layout;
use MoonShine\Laravel\Pages\LoginPage as BaseLoginPage;

#[Layout(CustomLoginLayout::class)]
class CustomLoginPage extends BaseLoginPage {}
