<x-layout title="Próximamente" nav="proximamente">
    <h1 class="text-2xl font-extrabold">Próximamente</h1>
    <p class="text-sm text-muted mt-1 mb-4">Juegos que todavía no han salido, por fecha de lanzamiento. Puedes ponerlos en Pendiente.</p>

    @if (! $ok && $juegos->isEmpty())
        <p class="text-center text-muted mt-10">No se pudo cargar el catálogo online ahora mismo. Prueba en un momento.</p>
    @elseif ($juegos->isEmpty())
        <p class="text-center text-muted mt-10">No hay lanzamientos próximos.</p>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            @foreach ($juegos as $g)
                <a href="{{ route('juego', $g) }}" class="block rounded-2xl overflow-hidden bg-surface border border-line relative">
                    <div class="aspect-[16/11] bg-surface-2">@if ($g->image)<img src="{{ $g->thumb(400, 260) }}" alt="" loading="lazy" class="w-full h-full object-cover !rounded-none !border-0 !p-0">@endif</div>
                    <span class="absolute top-2 left-2 chip bg-bg/90 text-white shadow">{{ $g->released->locale('es')->translatedFormat('j M Y') }}</span>
                    <div class="p-2.5">
                        <p class="text-sm font-bold leading-tight line-clamp-2 min-h-[2.2em]">{{ $g->name }}</p>
                        <p class="text-xs text-muted mt-1">{{ implode(', ', array_slice($g->genres ?? [], 0, 2)) }}</p>
                    </div>
                </a>
            @endforeach
        </div>
        @if ($hayMas)
            <a href="{{ route('proximamente', ['page' => $pagina + 1]) }}" class="btn-ghost w-full mt-5">Ver más lanzamientos</a>
        @endif
        @if ($pagina > 1)
            <a href="{{ route('proximamente', ['page' => $pagina - 1]) }}" class="block text-center text-sm text-accent mt-3">Página anterior</a>
        @endif
    @endif
</x-layout>
