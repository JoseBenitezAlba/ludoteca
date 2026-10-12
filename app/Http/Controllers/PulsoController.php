<?php

namespace App\Http\Controllers;

class PulsoController extends Controller
{
    /** Sirve el APK de Pulso desde la propia web (descarga directa, con tamaño conocido). */
    public function __invoke()
    {
        $ruta = storage_path('app/pulso.apk');
        if (! is_file($ruta) || filesize($ruta) < 1000) {
            @mkdir(dirname($ruta), 0775, true);
            file_put_contents($ruta, base64_decode(file_get_contents(resource_path('apk/pulso.b64'))));
        }

        return response()->download($ruta, 'pulso.apk', [
            'Content-Type' => 'application/vnd.android.package-archive',
            'Cache-Control' => 'no-cache',
        ]);
    }
}
