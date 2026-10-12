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
            // por fecha de lanzamiento; los que no tienen fecha, siempre al final
            'anio' => $entries->sort(fn ($a, $b) => $this->porFecha($a, $b, -1)),
            'anio_asc' => $entries->sort(fn ($a, $b) => $this->porFecha($a, $b, 1)),
            default => $entries->sortByDesc('updated_at'),
        };
        $entries = $ordenados->values();

        $conteo = auth()->user()->entries()->selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');

        return view('biblioteca', compact('entries', 'conteo', 'estado', 'orden'));
    }

    private function porFecha(Entry $a, Entry $b, int $sentido): int
    {
        $fa = $a->game->released?->getTimestamp();
        $fb = $b->game->released?->getTimestamp();
        if ($fa === null || $fb === null) {
            return ($fa === null) <=> ($fb === null);
        }

        return ($fa <=> $fb) * $sentido;
    }
}
