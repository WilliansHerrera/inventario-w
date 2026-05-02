<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\Models\PosVersion;
use App\Services\GitHubUpdateService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Process;
use MoonShine\Laravel\Pages\Page;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\Badge;
use MoonShine\UI\Components\Heading;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\Flex;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Components\Layout\LineBreak;
use MoonShine\UI\Components\Table\TableBuilder;
use MoonShine\UI\Fields\Password;
use MoonShine\UI\Fields\Text;

class SystemUpdatePage extends Page
{
    public function getTitle(): string
    {
        return __('Centro de Actualizaciones (Web & POS)');
    }

    public function components(): array
    {
        $service = app(GitHubUpdateService::class);
        $service->syncCurrentLocalVersion(); // Autopoblar si está vacío

        $commits = Cache::remember('web_latest_commits', 3600, fn () => $service->getWebLatestCommits());
        $posVersions = PosVersion::orderBy('release_date', 'desc')->get();

        // Obtener versión actual local (git hash) de forma segura
        $currentCommit = __('Desconocido');
        $hasLocalChanges = false;

        try {
            // Determinar ejecutable de Git (soporte para portable en subfolder o sistema)
            $gitPath = base_path('..\server\git\cmd\git.exe');
            $git = file_exists($gitPath) ? $gitPath : 'git';

            $commitProcess = Process::run("$git rev-parse --short HEAD");
            if ($commitProcess->successful()) {
                $currentCommit = trim($commitProcess->output());
            }

            $statusProcess = Process::run("$git status --porcelain");
            if ($statusProcess->successful()) {
                $hasLocalChanges = ! empty(trim($statusProcess->output()));
            }
        } catch (\Exception $e) {
            // Ignorar errores de git para no romper la página
            Log::warning('Git check failed: '.$e->getMessage());
        }

        return [
            Grid::make([
                // SECCIÓN WEB
                Column::make([
                    Box::make(__('Sistema Web (Laravel)'), [
                        Flex::make([
                            Heading::make(__('Versión Actual: ').$currentCommit)->h(4),
                            $hasLocalChanges
                                ? Badge::make(__('Cambios Locales Detectados'), 'warning')
                                : Badge::make(__('Repositorio Limpio'), 'success'),
                        ])->justifyAlign('between'),

                        LineBreak::make(),

                        Heading::make(__('Últimos cambios en GitHub:'))->h(5),

                        TableBuilder::make()
                            ->items($commits)
                            ->fields([
                                Text::make(__('Hash'), 'sha', fn ($c) => substr($c['sha'], 0, 7)),
                                Text::make(__('Cambios'), 'commit.message'),
                                Text::make(__('Hace'), 'commit.author.date', fn ($c) => Carbon::parse($c['commit']['author']['date'])->diffForHumans()),
                            ]),

                        LineBreak::make(),
                        Flex::make([
                            ActionButton::make(__('Buscar Mejoras Web'), fn () => route('admin.web.check-updates'))
                                ->primary()
                                ->icon('arrow-path'),

                            ActionButton::make(__('Actualizar Ahora'), fn () => route('admin.web.update-now'))
                                ->warning()
                                ->icon('cloud-arrow-down')
                                ->withConfirm(__('Confirmar Actualización'), __('¿Estás seguro? El sistema se actualizará desde GitHub y correrá las migraciones.'), __('Actualizar Ahora')),
                        ])->justifyAlign('start'),

                        LineBreak::make(),
                        Heading::make(__('Herramientas de Limpieza'))->h(5),
                        ActionButton::make(__('Empezar de Cero (Reset)'), fn () => route('admin.system.factory-reset'))
                            ->error()
                            ->icon('trash')
                            ->withConfirm(
                                __('BORRADO TOTAL'),
                                __('Esta acción eliminará todos los datos. El sistema se reiniciará con el usuario admin/admin. ¿Confirmar?'),
                                __('Resetear Sistema'),
                                fn () => [
                                    Password::make(__('Confirmar con Contraseña de Admin'), 'password')
                                        ->required(),
                                ]
                            ),
                    ]),
                ])->columnSpan(6),

                // SECCIÓN POS
                Column::make([
                    Box::make(__('Terminales de Windows (POS)'), [
                        Heading::make(__('Control de Versiones y Descarga'))->h(4),
                        LineBreak::make(),

                        Flex::make([
                            ActionButton::make(__('Descargar Instalador POS'), fn () => route('admin.pos.download'))
                                ->primary()
                                ->icon('cloud-arrow-down'),
                        ]),

                        LineBreak::make(),
                        TableBuilder::make()
                            ->items($posVersions)
                            ->fields([
                                Text::make(__('Versión'), 'version'),
                                Text::make(__('Estado'), 'is_latest', fn ($v) => $v->is_latest
                                    ? Badge::make(__('ACTUAL'), 'success')
                                    : Badge::make(__('Antigua'), 'gray')),
                                Text::make(__('Fecha'), 'release_date', fn ($v) => $v->release_date ? $v->release_date->format('d/m/Y') : 'N/A'),
                            ]),

                        LineBreak::make(),
                        Flex::make([
                            ActionButton::make(__('Sincronizar GitHub'), fn () => route('admin.pos.sync-github'))
                                ->success()
                                ->icon('hashtag'),
                        ])->justifyAlign('start'),
                    ]),
                ])->columnSpan(6),
            ]),
        ];
    }
}
