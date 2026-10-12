<x-layout :title="$lista->name" nav="listas">
    <h1 class="text-2xl font-extrabold tracking-tight">{{ $lista->name }}</h1>
    <p class="text-sm text-muted mb-4">por <a class="underline" href="{{ route('perfil', $lista->user) }}">{{ $lista->user->name }}</a>{{ $lista->description ? ' · '.$lista->description : '' }}</p>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
        @foreach ($lista->games as $g)
            <div class="relative">
                <x-game-card :game="$g" />
                @if (auth()->id() === $lista->user_id)
                    <form method="post" action="{{ route('listas.quitar', [$lista, $g]) }}" class="absolute top-2 right-2">@csrf @method('DELETE')<button class="w-7 h-7 rounded-full bg-bg/80 text-white text-sm" aria-label="Quitar">✕</button></form>
                @endif
            </div>
        @endforeach
    </div>
    @if ($lista->games->isEmpty())<p class="text-muted text-center mt-8">Lista vacía. Añade juegos desde su ficha.</p>@endif
    @if (auth()->id() === $lista->user_id)
        <form method="post" action="{{ route('listas.destroy', $lista) }}" class="mt-8 text-center" onsubmit="return confirm('¿Borrar la lista?')">@csrf @method('DELETE')<button class="btn-danger">Borrar lista</button></form>
    @endif
</x-layout>
