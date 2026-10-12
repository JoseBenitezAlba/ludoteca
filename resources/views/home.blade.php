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

    <section class="mb-8" x-data="{ items: [], cargando: false, hecho: false,
        async pedir() { this.cargando = true; try { const r = await fetch('/api/sorprende', { headers: { Accept: 'application/json' } }); this.items = (await r.json()).items || []; } catch (e) { this.items = []; } this.cargando = false; this.hecho = true; } }">
        <div class="flex items-center justify-between mb-3">
            <h2 class="h-section">Sorpréndeme</h2>
            <button type="button" @click="pedir()" :disabled="cargando" class="btn-primary !py-2" x-text="cargando ? 'Buscando…' : (hecho ? 'Otra tanda' : 'Sorpréndeme')"></button>
        </div>
        <p x-show="!hecho" class="text-sm text-muted">@auth Te propongo juegos que no tienes, según lo que juegas y mejor puntúas.@else Te propongo juegos populares y bien valorados.@endauth</p>
        <p x-show="hecho && !items.length" x-cloak class="text-sm text-muted">Ahora mismo no se me ocurre nada, prueba otra vez.</p>
        <div x-show="items.length" x-cloak class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            <template x-for="g in items" :key="g.slug">
                <a :href="'/juegos/' + g.slug" class="block rounded-2xl overflow-hidden bg-surface border border-line">
                    <div class="aspect-[16/11] bg-surface-2"><img :src="g.image" alt="" loading="lazy" class="w-full h-full object-cover !rounded-none !border-0 !p-0"></div>
                    <div class="p-2.5">
                        <p class="text-sm font-bold leading-tight line-clamp-2 min-h-[2.2em]" x-text="g.name"></p>
                        <p class="text-xs text-muted mt-1" x-text="[g.year, g.nota ? g.nota + ' crítica' : ''].filter(Boolean).join(' · ')"></p>
                        <p class="text-[11px] text-accent mt-0.5" x-text="g.motivo"></p>
                    </div>
                </a>
            </template>
        </div>
    </section>

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
