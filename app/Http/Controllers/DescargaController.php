<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class DescargaController extends Controller
{
    private const ORIGEN = 'https://github.com/JoseBenitezAlba/ludoteca/releases/download/apk/ludoteca.apk';

    /** Sirve la app de Android desde la propia web (descarga directa, con tamaño conocido). */
    public function __invoke()
    {
        $ruta = storage_path('app/ludoteca.apk');
        if (! is_file($ruta) || filesize($ruta) < 1_000_000 || filemtime($ruta) < now()->subHours(6)->getTimestamp()) {
            $r = Http::timeout(60)->withOptions(['allow_redirects' => true])->get(self::ORIGEN);
            if ($r->successful() && strlen($r->body()) > 1_000_000) {
                @mkdir(dirname($ruta), 0775, true);
                file_put_contents($ruta, $r->body());
            } elseif (! is_file($ruta)) {
                abort(503, 'No se pudo preparar la descarga, prueba en un minuto.');
            }
        }

        return response()->download($ruta, 'ludoteca.apk', [
            'Content-Type' => 'application/vnd.android.package-archive',
            'Cache-Control' => 'no-cache',
        ]);
    }
}
