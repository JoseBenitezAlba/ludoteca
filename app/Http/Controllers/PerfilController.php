<?php

namespace App\Http\Controllers;

use App\Models\User;
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

    public function propio()
    {
        return redirect()->route('perfil', auth()->user());
    }
}
