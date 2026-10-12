<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use Illuminate\Http\Request;

class BibliotecaController extends Controller
{
    public function __invoke(Request $request)
    {
        $estado = $request->query('estado');
        $orden = $request->query('orden', 'reciente');

        $query = auth()->user()->entries()->with('game');
        if (array_key_exists($estado, Entry::STATUSES)) {
            $query->where('status', $estado);
        }
        $entries = $query->get();

        $ordenados = match ($orden) {
            'nota' => $entries->sortByDesc(fn ($e) => $e->rating ?? 0),
            'nombre' => $entries->sortBy(fn ($e) => $e->game->name),
            default => $entries->sortByDesc('updated_at'),
        };
        $entries = $ordenados->values();

        $conteo = auth()->user()->entries()->selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');

        return view('biblioteca', compact('entries', 'conteo', 'estado', 'orden'));
    }
}
