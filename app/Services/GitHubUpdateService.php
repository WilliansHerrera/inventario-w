<?php

namespace App\Services;

use App\Models\PosVersion;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class GitHubUpdateService
{
    protected string $repo = 'WilliansHerrera/inventario-w';
    protected string $apiBase = 'https://api.github.com/repos/';

    /**
     * Sincronizar versiones del POS desde los Releases de GitHub.
     */
    public function syncPosFromGithub(): array
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'Inventario-W-App'
            ])->get($this->apiBase . $this->repo . '/releases');

            if (!$response->successful()) {
                throw new \Exception("Error al conectar con GitHub: " . $response->body());
            }

            $releases = $response->json();
            $syncedCount = 0;

            foreach ($releases as $release) {
                // Buscar el asset del instalador (.exe)
                $exeAsset = collect($release['assets'])->first(function ($asset) {
                    return str_ends_with($asset['name'], '.exe');
                });

                if (!$exeAsset) continue;

                $version = PosVersion::updateOrCreate(
                    ['version' => $release['tag_name']],
                    [
                        'changelog'    => $release['body'],
                        'filename'     => $exeAsset['browser_download_url'], // Guardamos la URL directa de GitHub
                        'release_date' => $release['published_at'],
                    ]
                );

                if ($release === $releases[0]) {
                    $version->update(['is_latest' => true]);
                }

                $syncedCount++;
            }

            return ['success' => true, 'count' => $syncedCount];
        } catch (\Exception $e) {
            Log::error("GitHub POS Sync failed: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Sincronizar estado del sistema Web desde los Commits de GitHub.
     */
    public function getWebLatestCommits(): array
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/vnd.github.v3+json',
                'User-Agent' => 'Inventario-W-App'
            ])->get($this->apiBase . $this->repo . '/commits', ['per_page' => 5]);

            if (!$response->successful()) {
                return [];
            }

            return $response->json();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Detectar la versión actual cargada en tauri.conf.json.
     */
    public function syncCurrentLocalVersion(): void
    {
        $path = base_path('POS-Windows/src-tauri/tauri.conf.json');
        if (File::exists($path)) {
            $config = json_decode(File::get($path), true);
            $versionNum = $config['package']['version'] ?? '1.0.0';

            // Verificamos si ya existe esta versión
            $exists = PosVersion::where('version', $versionNum)->exists();

            if (!$exists) {
                // Si no existe, la creamos y la marcamos como latest si no hay ninguna otra latest
                $hasLatest = PosVersion::where('is_latest', true)->exists();
                
                PosVersion::create([
                    'version'      => $versionNum,
                    'changelog'    => 'Versión detectada localmente (Código Fuente).',
                    'is_latest'    => !$hasLatest,
                    'release_date' => now(),
                    'filename'     => 'POS-Setup.exe' // Default local name
                ]);
            }
        }
    }
}
