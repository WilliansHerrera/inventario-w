<?php

namespace App\Console\Commands;

use App\Models\Producto;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportVtexProductsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:despensa {--limit=50 : Cantidad máxima de productos a importar} {--category= : ID de categoría opcional}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Importa productos masivamente desde la API de La Despensa de Don Juan, descargando sus imágenes.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $categoryId = $this->option('category');

        $this->info("Iniciando importación masiva de productos (Límite: $limit)");

        // Asegurar que los directorios existan
        Storage::disk('public')->makeDirectory('productos/principal');
        Storage::disk('public')->makeDirectory('productos/galeria');

        $baseUrl = 'https://www.ladespensadedonjuan.com.sv/api/catalog_system/pub/products/search';
        $params = [];
        if ($categoryId) {
            $params['fq'] = "C:/$categoryId/";
        }

        $importedCount = 0;
        $batchSize = 50; // VTEX max per request is 50
        $from = 0;

        while ($importedCount < $limit) {
            $to = min($from + $batchSize - 1, $limit - 1);
            
            $this->line("Consultando API VTEX: productos $from al $to...");
            
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'Accept' => 'application/json',
            ])->get($baseUrl, array_merge($params, [
                '_from' => $from,
                '_to' => $to
            ]));

            if (!$response->successful()) {
                $this->error("Error en la API: " . $response->status());
                break;
            }

            $products = $response->json();

            if (empty($products)) {
                $this->info("No se encontraron más productos en la API.");
                break;
            }

            foreach ($products as $vtexProduct) {
                if ($importedCount >= $limit) {
                    break 2;
                }

                $this->processProduct($vtexProduct);
                $importedCount++;
            }

            $from += $batchSize;
        }

        $this->info("¡Importación finalizada! Se procesaron $importedCount productos.");
    }

    private function processProduct(array $vtexProduct)
    {
        $nombre = $vtexProduct['productName'];
        // Tomar el primer item (SKU)
        $item = $vtexProduct['items'][0] ?? null;

        if (!$item) {
            $this->warn("Saltando producto sin items: $nombre");
            return;
        }

        // Obtener precio del primer seller
        $seller = $item['sellers'][0] ?? null;
        $precioVenta = $seller['commertialOffer']['Price'] ?? 0;

        if ($precioVenta <= 0) {
            $this->warn("Saltando producto sin precio: $nombre");
            return;
        }

        $ean = $item['ean'] ?? null;
        $sku = $vtexProduct['productReference'] ?? $vtexProduct['productId'];
        
        // Limpiar el SKU si viene como GTIN-xxx
        $sku = str_replace('GTIN-', '', $sku);

        // Si EAN está vacío, usar SKU
        if (empty($ean)) {
            $ean = $sku;
        }

        // Simular un margen del 30% (Costo = Precio / 1.30) -> Margen ganancia 30%
        // El usuario dijo que el precio web ya tiene IVA y es el final.
        // Formula: precio_venta = costo * (1 + margen/100) -> costo = precio_venta / 1.3
        $costo = round($precioVenta / 1.30, 2);
        $margen = 30;

        $producto = Producto::where(function ($query) use ($sku, $ean) {
            $query->where('sku', $sku);
            if (!empty($ean) && $ean !== $sku) {
                $query->orWhere('codigo_barra', $ean);
            }
        })->first();

        if ($producto) {
            $this->line("Producto actualizado: $nombre");
        } else {
            $producto = new Producto();
            $this->info("Nuevo producto creado: $nombre");
        }

        $producto->nombre = Str::limit($nombre, 255);
        $producto->sku = $sku;
        $producto->codigo_barra = $ean;
        $producto->precio = $costo;
        $producto->margen = $margen;
        $producto->precio_venta = $precioVenta;
        $producto->descripcion = strip_tags($vtexProduct['description'] ?? '');

        // Procesamiento de Imágenes
        $images = $item['images'] ?? [];
        if (!empty($images)) {
            // Imagen principal (la primera)
            $mainImageUrl = $images[0]['imageUrl'];
            $mainFileName = "productos/principal/{$sku}_main.jpg";
            
            if (!$producto->imagen || !Storage::disk('public')->exists($producto->imagen)) {
                $this->downloadImage($mainImageUrl, $mainFileName);
                $producto->imagen = $mainFileName;
            }

            // Galería (el resto)
            $galeria = $producto->galeria ?? [];
            for ($i = 1; $i < count($images); $i++) {
                $galeriaUrl = $images[$i]['imageUrl'];
                $galeriaFileName = "productos/galeria/{$sku}_$i.jpg";
                
                if (!in_array($galeriaFileName, $galeria)) {
                    if ($this->downloadImage($galeriaUrl, $galeriaFileName)) {
                        $galeria[] = $galeriaFileName;
                    }
                }
            }
            $producto->galeria = $galeria;
        }

        $producto->save();
    }

    private function downloadImage(string $url, string $path): bool
    {
        try {
            // Limpiar la URL de VTEX (quitar query strings como ?v=...)
            $url = explode('?', $url)[0];
            $imageContent = Http::timeout(10)->get($url)->body();
            if ($imageContent) {
                Storage::disk('public')->put($path, $imageContent);
                return true;
            }
        } catch (\Exception $e) {
            $this->error("Error descargando imagen: $url - " . $e->getMessage());
        }
        return false;
    }
}
