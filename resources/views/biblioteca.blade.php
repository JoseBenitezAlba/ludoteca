<x-layout title="Biblioteca" nav="biblioteca">
    <div class="flex items-end justify-between mb-3">
        <h1 class="text-2xl font-extrabold tracking-tight">Mi biblioteca</h1>
        <div class="flex gap-3 text-sm"><a href="/listas" class="text-accent font-semibold">Listas</a></div>
    </div>
    <div class="scroll-x mb-3">
        <a href="{{ route('biblioteca', ['orden' => $orden]) }}" class="chip !text-sm !px-3.5 !py-2 shrink-0 {{ !$estado ? 'bg-accent/25 text-white border border-accent' : 'bg-surface-2 text-muted border border-line' }}">Todos {{ $conteo->sum() }}</a>
        @foreach (\App\Models\Entry::STATUSES as $k => $label)
            <a href="{{ route('biblioteca', ['estado' => $k, 'orden' => $orden]) }}" class="chip !text-sm !px-3.5 !py-2 shrink-0 {{ $estado === $k ? 'bg-accent/25 text-white border border-accent' : 'bg-surface-2 text-muted border border-line' }}">{{ $label }} {{ $conteo[$k] ?? 0 }}</a>
        @endforeach
    </div>
    <div class="flex gap-2 text-xs text-muted mb-4">
        <span>Ordenar:</span>
        @foreach (['reciente' => 'Recientes', 'nota' => 'Nota', 'nombre' => 'A-Z', 'anio' => 'Año'] as $k => $l)
            @php
                $activo = $k === 'anio' ? in_array($orden, ['anio', 'anio_asc'], true) : $orden === $k;
                // tocar "Año" estando activo alterna entre más nuevos primero y más antiguos primero
                $destino = $k === 'anio' && $orden === 'anio' ? 'anio_asc' : $k;
                $flecha = $k === 'anio' && $activo ? ($orden === 'anio_asc' ? ' ↑' : ' ↓') : '';
            @endphp
            <a href="{{ route('biblioteca', array_filter(['estado' => $estado, 'orden' => $destino])) }}" class="{{ $activo ? 'text-white font-bold' : '' }}">{{ $l }}{{ $flecha }}</a>
        @endforeach
    </div>

    @forelse ($entries as $e)
        @if ($loop->first)<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">@endif
        <x-game-card :game="$e->game" :status="$e->status" :stars="$e->stars()" />
        @if ($loop->last)</div>@endif
    @empty
        <div class="card p-8 text-center">
            <p class="font-semibold">Aquí no hay nada todavía.</p>
            <a href="{{ route('buscar') }}" class="btn-primary mt-3">Buscar un juego</a>
        </div>
    @endforelse
</x-layout>
