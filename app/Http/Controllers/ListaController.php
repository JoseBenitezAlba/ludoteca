<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameList;
use Illuminate\Http\Request;

class ListaController extends Controller
{
    public function index()
    {
        $listas = auth()->user()->lists()->withCount('games')->with(['games' => fn ($q) => $q->limit(4)])->latest()->get();

        return view('listas.index', compact('listas'));
    }

    public function store(Request $request)
    {
        $datos = $request->validate(['name' => 'required|string|max:80', 'description' => 'nullable|string|max:240']);
        $lista = auth()->user()->lists()->create($datos);

        return redirect()->route('listas.show', $lista)->with('ok', 'Lista creada.');
    }

    public function show(GameList $lista)
    {
        $lista->load('games', 'user');

        return view('listas.show', compact('lista'));
    }

    public function destroy(GameList $lista)
    {
        abort_unless($lista->user_id === auth()->id(), 403);
        $lista->delete();

        return redirect()->route('listas.index')->with('ok', 'Lista borrada.');
    }

    public function agregar(Request $request, Game $game)
    {
        $lista = auth()->user()->lists()->findOrFail($request->input('lista'));
        $lista->games()->syncWithoutDetaching([$game->id]);

        return back()->with('ok', "Añadido a «{$lista->name}».");
    }

    public function quitar(GameList $lista, Game $game)
    {
        abort_unless($lista->user_id === auth()->id(), 403);
        $lista->games()->detach($game->id);

        return back();
    }
}
