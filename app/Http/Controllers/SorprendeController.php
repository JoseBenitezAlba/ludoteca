<?php

namespace App\Http\Controllers;

use App\Services\Recomendador;

class SorprendeController extends Controller
{
    public function __invoke(Recomendador $recomendador)
    {
        [$juegos, $motivos] = $recomendador->sugerir(auth()->user(), 6);

        return response()->json(['items' => $juegos->map(fn ($g) => [
            'slug' => $g->slug,
            'name' => $g->name,
            'image' => $g->thumb(400, 260),
            'year' => $g->year(),
            'motivo' => $motivos[$g->id] ?? '',
            'nota' => $g->critica(),
        ])->values()]);
    }
}
