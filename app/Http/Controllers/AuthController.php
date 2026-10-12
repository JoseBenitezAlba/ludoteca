<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function entrar(Request $request)
    {
        $datos = $request->validate(['email' => 'required|email', 'password' => 'required']);

        if (! Auth::attempt($datos, true)) {
            return back()->withErrors(['email' => 'Email o contraseña incorrectos.'])->onlyInput('email');
        }
        $request->session()->regenerate();

        return redirect()->intended('/biblioteca');
    }

    public function registro()
    {
        return view('auth.registro');
    }

    public function registrar(Request $request)
    {
        $request->merge(['username' => mb_strtolower(trim((string) $request->input('username')))]);

        $datos = $request->validate([
            'name' => 'required|string|max:60',
            'username' => ['required', 'string', 'min:3', 'max:20', 'regex:/^[a-z0-9_]+$/', 'unique:users,username'],
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ], [
            'username.regex' => 'Solo letras, números y guion bajo.',
            'username.unique' => 'Ese usuario ya existe.',
            'email.unique' => 'Ya hay una cuenta con ese email.',
            'password.min' => 'Mínimo 8 caracteres.',
        ]);

        $user = User::create($datos);
        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect('/buscar')->with('ok', 'Cuenta creada. Busca tu primer juego.');
    }

    public function demo(Request $request)
    {
        $user = User::where('username', 'demo')->firstOrFail();
        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/biblioteca');
    }

    public function salir(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
