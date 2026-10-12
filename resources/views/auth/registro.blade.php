<x-layout title="Crear cuenta">
    <div class="max-w-sm mx-auto mt-6">
        <h1 class="text-2xl font-extrabold tracking-tight mb-4">Crear cuenta</h1>
        <form method="post" action="/registro" class="card p-4 space-y-3">
            @csrf
            @foreach ([['name', 'Nombre', 'text', 'name'], ['username', 'Usuario (ej. jose_91)', 'text', 'username'], ['email', 'Email', 'email', 'email'], ['password', 'Contraseña (mín. 8)', 'password', 'new-password']] as [$n, $ph, $t, $ac])
                <div><input type="{{ $t }}" name="{{ $n }}" value="{{ $t === 'password' ? '' : old($n) }}" placeholder="{{ $ph }}" required autocomplete="{{ $ac }}">@error($n)<p class="text-xs text-abandoned mt-1">{{ $message }}</p>@enderror</div>
            @endforeach
            <button class="btn-primary w-full">Crear cuenta</button>
        </form>
        <p class="text-center text-sm text-muted mt-4">¿Ya tienes cuenta? <a class="text-accent font-semibold" href="{{ route('login') }}">Entra</a></p>
    </div>
</x-layout>
