<x-layout title="Entrar">
    <div class="max-w-sm mx-auto mt-6">
        <h1 class="text-2xl font-extrabold tracking-tight mb-4">Entrar</h1>
        <form method="post" action="/entrar" class="card p-4 space-y-3">
            @csrf
            <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required autocomplete="email">
            <input type="password" name="password" placeholder="Contraseña" required autocomplete="current-password">
            @error('email')<p class="text-sm text-abandoned">{{ $message }}</p>@enderror
            <button class="btn-primary w-full">Entrar</button>
        </form>
        <p class="text-center text-sm text-muted mt-4">¿Sin cuenta? <a class="text-accent font-semibold" href="{{ route('registro') }}">Crea una</a> o <a class="text-accent font-semibold" href="{{ route('demo') }}">prueba la demo</a></p>
    </div>
</x-layout>
