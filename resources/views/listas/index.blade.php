<x-layout title="Listas" nav="listas">
    <h1 class="text-2xl font-extrabold tracking-tight mb-4">Mis listas</h1>
    <form method="post" action="{{ route('listas.store') }}" class="card p-4 mb-5 space-y-3">
        @csrf
        <input name="name" required maxlength="80" placeholder="Nombre de la lista (ej. Para el verano)">
        <input name="description" maxlength="240" placeholder="Descripción (opcional)">
        <button class="btn-primary">Crear lista</button>
    </form>
    <div class="space-y-3">
        @forelse ($listas as $l)
            <a href="{{ route('listas.show', $l) }}" class="card p-3 flex gap-3 items-center">
                <div class="flex -space-x-5">@foreach ($l->games as $g)<img src="{{ $g->thumb(160, 160) }}" class="w-14 h-14 object-cover !rounded-xl !p-0 border-2 !border-surface" alt="">@endforeach</div>
                <div class="min-w-0"><p class="font-bold truncate">{{ $l->name }}</p><p class="text-xs text-muted">{{ $l->games_count }} juegos</p></div>
            </a>
        @empty
            <p class="text-muted text-center">Todavía no tienes listas.</p>
        @endforelse
    </div>
</x-layout>
