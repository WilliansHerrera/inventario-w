<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Locale;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class VerifySyncSignature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $id = $request->header('X-Sync-ID');
        $timestamp = $request->header('X-Sync-Timestamp');
        $signature = $request->header('X-Sync-Signature');

        if (!$id || !$timestamp || !$signature) {
            return response()->json([
                'success' => false,
                'error' => 'Seguridad: Faltan parámetros de firma digital (ID/Timestamp/Signature).'
            ], 401);
        }

        // 1. Validar ventana de tiempo (5 minutos) para evitar Replay Attacks
        if (abs(time() - (int) $timestamp) > 300) {
            return response()->json([
                'success' => false,
                'error' => 'Seguridad: La firma ha expirado. Sincroniza la hora de tu terminal.'
            ], 401);
        }

        // 2. Buscar Sucursal/Locale
        $locale = null;
        if ($id) {
            $locale = Locale::find($id);
        }
        
        if (!$locale) {
            $token = $request->header('X-Sync-Token');
            if ($token) {
                $locale = Locale::where('sync_token', $token)->first();
            }
        }

        if (!$locale || empty($locale->sync_token)) {
            return response()->json([
                'success' => false,
                'error' => 'Seguridad: Sucursal no identificada o sin token de seguridad configurado.'
            ], 401);
        }


        // 3. Reconstruir mensaje para verificar firma
        // Formato: timestamp + method + path + body
        $method = strtoupper($request->method());
        $path = '/' . ltrim($request->path(), '/');
        $body = $request->getContent(); // Raw body

        $message = $timestamp . $method . $path . $body;
        $calculatedSignature = hash_hmac('sha256', $message, $locale->sync_token);

        // 4. Comparar firmas (Timing safe comparison)
        if (!hash_equals($calculatedSignature, $signature)) {
            Log::warning("Falla de firma digital para Sucursal #{$id}", [
                'received' => $signature,
                'calculated' => $calculatedSignature,
                'message_base' => $message,
                'token_used' => substr($locale->sync_token, 0, 4) . '***'
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Seguridad: Firma digital inválida. El contenido podría haber sido manipulado.'
            ], 401);
        }

        // Guardar el locale en el request para uso posterior si es necesario
        $request->attributes->set('sync_locale', $locale);

        return $next($request);
    }
}
