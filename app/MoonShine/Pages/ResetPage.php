<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use MoonShine\Laravel\Pages\Page;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Fields\Password;

class ResetPage extends Page
{
    public function getTitle(): string
    {
        return __('Empezar de Cero (Reset del Sistema)');
    }

    // Permite que cualquier usuario administrativo acceda a esta página de mantenimiento
    protected bool $withoutPermissions = true;

    protected function components(): iterable
    {
        return [
            ActionButton::make(__('Empezar de Cero'), fn() => route('admin.system.factory-reset'))
                ->error()
                ->icon('trash')
                ->method('post')
                ->withConfirm(
                    __('BORRADO TOTAL'),
                    __('Esta acción eliminará todos los datos operativos (ventas, stock, cajas). El sistema se mantendrá con su configuración básica y usuarios. ¿Confirmar?'),
                    __('Resetear Sistema'),
                    [Password::make(__('Confirmar con Contraseña de Admin'), 'password')->required()]
                ),
        ];
    }
}
