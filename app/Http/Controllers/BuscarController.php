<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use App\Services\Catalogo;
use Illuminate\Http\Request;

class BuscarController extends Controller
{
    public function index()
    {
        return view('buscar', [
            'generos' => array_keys(Catalogo::GENEROS),
            'plataformas' => array_keys(Catalogo::PLATAFORMAS),
        ]);
    }

    /** src=local responde al instante; src=remoto consulta RAWG con timeout corto. */
    public function api(Request $request, Catalogo $catalogo)
    {
        $q = trim((string) $request->query('q', ''));
        $src = $request->query('src', 'local');

        $f = $request->query();
        $juegos = $src === 'remoto' ? $catalogo->buscarRemoto($q, $f) : $catalogo->buscarLocal($q, $f);
        if ($juegos === null) {
            return response()->json(['ok' => false, 'items' => []]);
        }

        $estados = auth()->check()
            ? Entry::where('user_id', auth()->id())->whereIn('game_id', $juegos->pluck('id'))->pluck('status', 'game_id')
            : collect();

        return response()->json([
            'ok' => true,
            'items' => $juegos->map(fn ($g) => [
                'slug' => $g->slug,
                'name' => $g->name,
                'image' => $g->thumb(400, 260),
                'year' => $g->year(),
                'genres' => array_slice($g->genres ?? [], 0, 2),
                'status' => $estados[$g->id] ?? null,
            ])->values(),
        ]);
    }
}
