<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Services\Estadisticas;

class PerfilController extends Controller
{
    public function show(User $user, Estadisticas $estadisticas)
    {
        $stats = $estadisticas->de($user);
        $listas = $user->lists()->withCount('games')->get();
        $recientes = $user->entries()->with('game')->latest('updated_at')->limit(8)->get();

        return view('perfil', compact('user', 'stats', 'listas', 'recientes'));
    }

    public function detalle(User $user, Request $request, Estadisticas $estadisticas)
    {
        $tipo = (string) $request->query('tipo', 'horas');
        $valor = (string) $request->query('valor', '');
        [$entries, $titulo] = $estadisticas->detalle($user, $tipo, $valor);
        $horas = $entries->sum(fn ($e) => $estadisticas->horasDe($e));

        return view('perfil-detalle', ['user' => $user, 'entries' => $entries, 'titulo' => $titulo, 'horas' => $horas, 'est' => $estadisticas]);
    }

    public function propio()
    {
        return redirect()->route('perfil', auth()->user());
    }
}
