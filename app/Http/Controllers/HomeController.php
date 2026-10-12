<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use App\Models\Game;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __invoke()
    {
        $user = auth()->user();

        $jugando = $user
            ? $user->entries()->with('game')->where('status', 'playing')->latest('updated_at')->limit(6)->get()
            : collect();

        $populares = Game::whereNotNull('image')->orderByDesc('ratings_count')->limit(12)->get();

        // mejores segun la comunidad de la app (minimo 2 votos)
        $comunidad = Entry::select('game_id', DB::raw('avg(rating) as media'), DB::raw('count(rating) as votos'))
            ->whereNotNull('rating')->groupBy('game_id')->havingRaw('count(rating) >= 2')
            ->orderByDesc('media')->limit(8)->get();
        $juegosComunidad = Game::whereIn('id', $comunidad->pluck('game_id'))->get()->keyBy('id');
        $comunidad = $comunidad->filter(fn ($c) => $juegosComunidad->has($c->game_id))
            ->map(fn ($c) => ['game' => $juegosComunidad[$c->game_id], 'media' => round($c->media / 2, 1), 'votos' => $c->votos]);

        return view('home', compact('jugando', 'populares', 'comunidad'));
    }
}
