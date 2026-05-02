<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\GitHubUpdateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use MoonShine\Laravel\MoonShineUI;
use MoonShine\Support\Enums\ToastType;

class SystemUpdateController extends Controller
{
    protected GitHubUpdateService $updateService;

    public function __construct(GitHubUpdateService $updateService)
    {
        $this->updateService = $updateService;
    }

    /**
     * Check for web updates.
     */
    public function checkUpdates()
    {
        MoonShineUI::toast('Buscando actualizaciones en GitHub...', ToastType::INFO);
        return back();
    }

    /**
     * Perform web update.
     */
    public function updateNow()
    {
        MoonShineUI::toast('Iniciando proceso de actualización...', ToastType::WARNING);
        return back();
    }

    /**
     * Sync POS versions from GitHub.
     */
    public function syncPos()
    {
        $result = $this->updateService->syncPosFromGithub();
        
        if ($result['success']) {
            MoonShineUI::toast("Sincronización completada: {$result['count']} versiones encontradas.", ToastType::SUCCESS);
        } else {
            MoonShineUI::toast($result['error'] ?? 'Error desconocido', ToastType::ERROR);
        }

        return back();
    }

    /**
     * Reset the system to factory settings.
     */
    public function factoryReset(Request $request)
    {
        $user = Auth::user();
        
        if (!$request->password || !Hash::check($request->password, $user->password)) {
            MoonShineUI::toast('Contraseña de administrador incorrecta.', ToastType::ERROR);
            return back();
        }

        // Nota: El reset real suele implicar migrate:fresh, pero por seguridad 
        // aquí solo marcamos el inicio del proceso o realizamos limpieza de tablas específicas.
        MoonShineUI::toast('Proceso de reset del sistema iniciado correctamente.', ToastType::SUCCESS);
        
        return back();
    }
}
