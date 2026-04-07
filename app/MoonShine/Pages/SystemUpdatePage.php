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
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Cache;
use MoonShine\UI\Fields\Text;

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
        
        // Obtener versión actual local (git hash)
        $currentCommit = "Desconocido";
        try {
            $currentCommit = trim(shell_exec('git rev-parse --short HEAD') ?? 'No Git detected');
        } catch(\Exception $e) {}

        return [
            Grid::make([
                // SECCIÓN WEB
                Column::make([
                    Box::make('Sistema Web (Laravel)', [
                        Heading::make('Versión Actual: ' . $currentCommit)->h(4),
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
                    ])
                ])->columnSpan(6),

                // SECCIÓN POS
                Column::make([
                    Box::make('Terminales de Windows (POS)', [
                        Heading::make('Control de Versiones de Ejecutables')->h(4),
                        LineBreak::make(),
                        
                        \MoonShine\UI\Components\FlexibleRender::make('Gestiona los archivos .exe que descargan las sucursales. El sistema sincroniza automáticamente las versiones desde los "Releases" de GitHub.'),
                        
                        LineBreak::make(),
                        Flex::make([
                            ActionButton::make('Sincronizar Releases', fn() => route('admin.pos.sync-github'))
                                ->success()
                                ->icon('hashtag'),
                        ])->justifyAlign('start'),
                    ])
                ])->columnSpan(6),
            ])
        ];
    }
}