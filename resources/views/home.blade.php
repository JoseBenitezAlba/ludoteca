<x-layout nav="home">
    @guest
    <section class="rounded-3xl p-6 md:p-10 mb-8 border border-line relative overflow-hidden" style="background: radial-gradient(120% 120% at 0% 0%, #2a1f5c 0%, #141824 55%, #0f2a33 100%)">
        <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight leading-[1.05]">Tu biblioteca de juegos,<br>con <span class="bg-clip-text text-transparent" style="background-image:linear-gradient(90deg,#a78bfa,#22d3ee)">todas las estadísticas gratis</span>.</h1>
        <p class="mt-3 text-muted max-w-xl">Marca lo que juegas, termina o abandonas. Puntúa con estrellas, crea listas y mira tus horas, géneros y medias. Sin planes de pago. La búsqueda siempre carga.</p>
        <div class="mt-5 flex flex-wrap gap-3">
            <a href="{{ route('registro') }}" class="btn-primary">Crear mi biblioteca</a>
            <a href="{{ route('demo') }}" class="btn-ghost">Ver una cuenta de ejemplo</a>
        </div>
    </section>
    @endguest

    <form action="{{ route('buscar') }}" class="mb-6">
        <input type="search" name="q" placeholder="Busca un juego…" class="!rounded-2xl !py-3.5">
    </form>

    @if ($jugando->isNotEmpty())
        <section class="mb-8">
            <h2 class="h-section mb-3">Jugando ahora</h2>
            <div class="scroll-x">
                @foreach ($jugando as $e)<x-game-card :game="$e->game" :wide="true" :stars="$e->stars()" />@endforeach
            </div>
        </section>
    @endif

    @if ($comunidad->isNotEmpty())
        <section class="mb-8">
            <h2 class="h-section mb-3">Mejor valorados por la comunidad</h2>
            <div class="scroll-x">
                @foreach ($comunidad as $c)<x-game-card :game="$c['game']" :wide="true" :stars="$c['media']" />@endforeach
            </div>
        </section>
    @endif

    <section>
        <h2 class="h-section mb-3">Populares</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            @foreach ($populares as $g)<x-game-card :game="$g" />@endforeach
        </div>
    </section>
</x-layout>
