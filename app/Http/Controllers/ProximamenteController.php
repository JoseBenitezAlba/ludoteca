<?php

namespace App\Http\Controllers;

use App\Services\Catalogo;
use Illuminate\Http\Request;

class ProximamenteController extends Controller
{
    public function __invoke(Request $request, Catalogo $catalogo)
    {
        $pagina = max(1, min(20, (int) $request->query('page', 1)));
        [$juegos, $hayMas, $ok] = $catalogo->proximos($pagina);

        return view('proximamente', compact('juegos', 'hayMas', 'ok', 'pagina'));
    }
}
