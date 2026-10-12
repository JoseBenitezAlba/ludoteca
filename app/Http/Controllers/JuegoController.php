<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use App\Models\Game;
use App\Services\Catalogo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JuegoController extends Controller
{
    public function show(Game $game, Catalogo $catalogo)
    {
        $catalogo->detallar($game);

        $stats = Entry::where('game_id', $game->id)->whereNotNull('rating')
            ->selectRaw('avg(rating) as media, count(*) as votos')->first();
        $comunidad = [
            'media' => $stats->votos ? round($stats->media / 2, 1) : null,
            'votos' => (int) $stats->votos,
            'jugadores' => Entry::where('game_id', $game->id)->count(),
        ];
        $entry = auth()->check() ? Entry::where('user_id', auth()->id())->where('game_id', $game->id)->first() : null;
        $listas = auth()->check() ? auth()->user()->lists()->get() : collect();
        $resenas = Entry::with('user')->where('game_id', $game->id)->whereNotNull('review')
            ->where('review', '!=', '')->latest()->limit(5)->get();

        return view('juego', compact('game', 'comunidad', 'entry', 'listas', 'resenas'));
    }

    public function guardar(Request $request, Game $game)
    {
        $datos = $request->validate([
            'status' => ['required', Rule::in(array_keys(Entry::STATUSES))],
            'rating' => 'nullable|integer|between:1,10',
            'hours' => 'nullable|numeric|between:0,9999',
            'platform' => 'nullable|string|max:60',
            'review' => 'nullable|string|max:2000',
            'finished_at' => 'nullable|date',
            'favorite' => 'nullable|boolean',
        ]);
        $datos['favorite'] = (bool) ($datos['favorite'] ?? false);

        Entry::updateOrCreate(['user_id' => auth()->id(), 'game_id' => $game->id], $datos);

        return back()->with('ok', 'Guardado en tu biblioteca.');
    }

    public function quitar(Game $game)
    {
        Entry::where('user_id', auth()->id())->where('game_id', $game->id)->delete();

        return back()->with('ok', 'Quitado de tu biblioteca.');
    }
}
