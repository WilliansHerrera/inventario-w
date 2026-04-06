<?php

declare(strict_types=1);

namespace App\MoonShine\Resources\Caja;

use App\Models\Caja;
use App\MoonShine\Resources\Caja\Pages\CajaIndexPage;
use App\MoonShine\Resources\Caja\Pages\CajaFormPage;
use App\MoonShine\Resources\Caja\Pages\CajaDetailPage;

use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Contracts\Core\PageContract;

/**
 * @extends ModelResource<Caja, CajaIndexPage, CajaFormPage, CajaDetailPage>
 */
class CajaResource extends ModelResource
{
    protected string $model = Caja::class;

    protected string $title = 'Cajas';
    
    protected string $column = 'nombre';

    /**
     * @return list<class-string<PageContract>>
     */
    protected function pages(): array
    {
        return [
            CajaIndexPage::class,
            CajaFormPage::class,
            CajaDetailPage::class,
        ];
    }

    public function rules($item): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'locale_id' => ['required', 'exists:locales,id'],
            'saldo' => ['required', 'numeric'],
        ];
    }

    protected function actions(): array
    {
        return [
            \MoonShine\UI\Components\ActionButton::make(
                'Configurar POS',
                '#'
            )
            ->icon('o-computer-desktop')
            ->primary()
            ->modal(
                title: fn($item) => "Guía de Conexión: {$item->nombre}",
                content: function($item) {
                    $url = config('app.url') . '/api/v1';
                    $json = json_encode([
                        'API_BASE' => $url,
                        'SYNC_TOKEN' => $item->sync_token,
                        'LOCAL_ID' => $item->locale_id,
                        'CAJA_ID' => $item->id
                    ], JSON_PRETTY_PRINT);

                    return "
                        <div class='space-y-4 p-2'>
                            <p class='text-sm text-slate-500'>Copia estos datos en la sección de Configuración de tu terminal POS Windows.</p>
                            
                            <div class='grid grid-cols-1 gap-3'>
                                <div>
                                    <label class='block text-xs font-bold uppercase text-slate-400'>URL de API</label>
                                    <code class='block p-2 bg-slate-100 rounded border text-xs overflow-x-auto'>$url</code>
                                </div>
                                <div>
                                    <label class='block text-xs font-bold uppercase text-slate-400'>Token de Sincronización</label>
                                    <code class='block p-2 bg-slate-100 rounded border text-xs break-all'>{$item->sync_token}</code>
                                </div>
                            </div>

                            <div class='pt-4'>
                                <label class='block text-xs font-black uppercase text-indigo-600 mb-1'>Código de Configuración Rápida</label>
                                <textarea readonly class='w-full p-3 bg-slate-900 text-indigo-300 font-mono text-[10px] rounded-xl border-none focus:ring-0 shadow-inner' rows='6'>$json</textarea>
                                <p class='text-[10px] text-slate-400 mt-1 italic'>Copia todo el texto anterior y pégalo en el campo 'Configuración JSON' de la aplicación POS.</p>
                            </div>
                        </div>
                    ";
                }
            )
            ->showInLine(),
        ];
    }
}
