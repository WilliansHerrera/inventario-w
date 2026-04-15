<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use MoonShine\Laravel\Pages\Page;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Components\Layout\Flex;
use MoonShine\UI\Components\Layout\Grid;
use MoonShine\UI\Components\Layout\Column;
use MoonShine\UI\Components\Layout\LineBreak;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\Heading;
use MoonShine\UI\Components\Table\TableBuilder;
use App\Services\GitHubUpdateService;
use App\Models\PosVersion;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Password;
use MoonShine\UI\Components\Badge;

class SystemUpdatePage extends Page
{
    public function getTitle(): string
    {
        return 'Centro de Actualizaciones (Web & POS)';
    }

    public function components(): array
    {
        $service = app(GitHubUpdateService::class);
        $service->syncCurrentLocalVersion(); // Autopoblar si está vacío

        $commits = Cache::remember('web_latest_commits', 3600, fn() => $service->getWebLatestCommits());
        $posVersions = PosVersion::orderBy('release_date', 'desc')->get();
        
        // Obtener versión actual local (git hash) de forma segura
        $currentCommit = "Desconocido";
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
                $hasLocalChanges = !empty(trim($statusProcess->output()));
            }
        } catch (\Exception $e) {
            // Ignorar errores de git para no romper la página
            Log::warning("Git check failed: " . $e->getMessage());
        }

        return [
            Grid::make([
                // SECCIÓN WEB
                Column::make([
                    Box::make('Sistema Web (Laravel)', [
                        Flex::make([
                            Heading::make('Versión Actual: ' . $currentCommit)->h(4),
                            $hasLocalChanges 
                                ? Badge::make('Cambios Locales Detectados', 'warning')
                                : Badge::make('Repositorio Limpio', 'success'),
                        ])->justifyAlign('between'),
                        
                        LineBreak::make(),
                        
                        Heading::make('Últimos cambios en GitHub:')->h(5),

                        TableBuilder::make()
                            ->items($commits)
                            ->fields([
                                Text::make('Hash', 'sha', fn($c) => substr($c['sha'], 0, 7)),
                                Text::make('Cambios', 'commit.message'),
                                Text::make('Hace', 'commit.author.date', fn($c) => \Carbon\Carbon::parse($c['commit']['author']['date'])->diffForHumans()),
                            ]),
                        
                        LineBreak::make(),
                        Flex::make([
                            ActionButton::make('Buscar Mejoras Web', fn() => route('admin.web.check-updates'))
                                ->primary()
                                ->icon('arrow-path'),
                            
                            ActionButton::make('Actualizar Ahora', fn() => route('admin.web.update-now'))
                                ->warning()
                                ->icon('cloud-arrow-down')
                                ->withConfirm('Confirmar Actualización', '¿Estás seguro? El sistema se actualizará desde GitHub y correrá las migraciones.', 'Actualizar Ahora'),
                        ])->justifyAlign('start'),

                        LineBreak::make(),
                        Heading::make('Herramientas de Limpieza')->h(5),
                        ActionButton::make('Empezar de Cero (Reset)', fn() => route('admin.system.factory-reset'))
                            ->error()
                            ->icon('trash')
                            ->withConfirm(
                                'BORRADO TOTAL', 
                                'Esta acción eliminará todos los datos. El sistema se reiniciará con el usuario admin/admin. ¿Confirmar?', 
                                'Resetear Sistema',
                                fn() => [
                                    Password::make('Confirmar con Contraseña de Admin', 'password')
                                        ->required()
                                ]
                            ),
                    ])
                ])->columnSpan(6),

                // SECCIÓN POS
                Column::make([
                    Box::make('Terminales de Windows (POS)', [
                        Heading::make('Control de Versiones y Descarga')->h(4),
                        LineBreak::make(),
                        
                        Flex::make([
                            ActionButton::make('Descargar Instalador POS', fn() => route('admin.pos.download'))
                                ->primary()
                                ->icon('cloud-arrow-down'),
                        ]),
                        
                        LineBreak::make(),
                        TableBuilder::make()
                            ->items($posVersions)
                            ->fields([
                                Text::make('Versión', 'version'),
                                Text::make('Estado', 'is_latest', fn($v) => $v->is_latest 
                                    ? Badge::make('ACTUAL', 'success') 
                                    : Badge::make('Antigua', 'gray')),
                                Text::make('Fecha', 'release_date', fn($v) => $v->release_date ? $v->release_date->format('d/m/Y') : 'N/A'),
                            ]),

                        LineBreak::make(),
                        Flex::make([
                            ActionButton::make('Sincronizar GitHub', fn() => route('admin.pos.sync-github'))
                                ->success()
                                ->icon('hashtag'),
                        ])->justifyAlign('start'),
                    ])
                ])->columnSpan(6),
            ])
        ];
    }
}