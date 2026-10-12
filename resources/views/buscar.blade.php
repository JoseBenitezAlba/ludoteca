<x-layout title="Buscar" nav="buscar">
    <div x-data="buscador(@js(request('q', '')))">
        <div class="flex gap-2 mb-3">
            <input type="search" x-model="q" @input.debounce.150ms="lanzar()" placeholder="Busca un juego…" autofocus autocomplete="off" class="!rounded-2xl !py-3.5 flex-1 min-w-0">
            <button type="button" @click="abierto = !abierto" class="btn-ghost shrink-0 !rounded-2xl relative" :class="activos && '!border-accent !text-white'" aria-label="Filtros">
                Filtros<span x-show="activos" x-cloak class="ml-1.5 inline-flex items-center justify-center min-w-5 h-5 px-1 rounded-full bg-accent text-[11px] font-bold" x-text="activos"></span>
            </button>
        </div>

        <div x-show="abierto" x-cloak x-transition class="card p-4 mb-3 space-y-4">
            <div>
                <p class="text-xs font-bold text-muted mb-2">Ordenar por</p>
                <div class="flex flex-wrap gap-2">
                    @foreach (['valoracion' => 'Valoración', 'anio' => 'Año (nuevos)', 'nombre' => 'Nombre A-Z'] as $k => $et)
                        <button type="button" @click="ordenar('{{ $k }}')" :class="o === '{{ $k }}' ? 'text-white border-accent bg-accent/25' : 'text-muted border-line bg-surface-2'" class="chip !text-sm !px-3 !py-1.5 border">{{ $et }}</button>
                    @endforeach
                </div>
            </div>
            <div>
                <p class="text-xs font-bold text-muted mb-2">Género</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($generos as $g)
                        <button type="button" @click="alternar('g', @js($g))" :class="f.g.includes(@js($g)) ? 'text-white border-accent bg-accent/25' : 'text-muted border-line bg-surface-2'" class="chip !text-sm !px-3 !py-1.5 border">{{ $g }}</button>
                    @endforeach
                </div>
            </div>
            <div>
                <p class="text-xs font-bold text-muted mb-2">Plataforma</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($plataformas as $pl)
                        <button type="button" @click="alternar('p', @js($pl))" :class="f.p.includes(@js($pl)) ? 'text-white border-accent bg-accent/25' : 'text-muted border-line bg-surface-2'" class="chip !text-sm !px-3 !py-1.5 border">{{ $pl }}</button>
                    @endforeach
                </div>
            </div>
            <div>
                <p class="text-xs font-bold text-muted mb-2">Año de lanzamiento</p>
                <div class="flex items-center gap-2">
                    <input type="number" inputmode="numeric" min="1970" max="2100" placeholder="Desde" x-model="f.desde" @input.debounce.500ms="lanzar()" class="!py-2 min-w-0">
                    <span class="text-muted">–</span>
                    <input type="number" inputmode="numeric" min="1970" max="2100" placeholder="Hasta" x-model="f.hasta" @input.debounce.500ms="lanzar()" class="!py-2 min-w-0">
                </div>
            </div>
            <div>
                <p class="text-xs font-bold text-muted mb-2">Metacritic mínimo</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ([60, 75, 85, 90] as $n)
                        <button type="button" @click="puntuar({{ $n }})" :class="f.mc === {{ $n }} ? 'text-white border-accent bg-accent/25' : 'text-muted border-line bg-surface-2'" class="chip !text-sm !px-3 !py-1.5 border">{{ $n }}+</button>
                    @endforeach
                </div>
            </div>
            <button type="button" x-show="activos" @click="limpiar()" class="text-sm text-accent">Limpiar filtros</button>
        </div>

        <div class="h-6 text-xs text-muted flex items-center gap-2 mb-2">
            <template x-if="cargandoRemoto"><span class="flex items-center gap-2"><span class="inline-block w-3 h-3 rounded-full border-2 border-accent border-t-transparent animate-spin"></span> Buscando en el catálogo online…</span></template>
            <template x-if="remotoFallo && !cargandoRemoto"><span>Catálogo online sin respuesta. Mostrando los juegos que ya tenemos guardados.</span></template>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
            <template x-for="g in items" :key="g.slug">
                <a :href="'/juegos/' + g.slug" class="block rounded-2xl overflow-hidden bg-surface border border-line relative">
                    <div class="aspect-[16/11] bg-surface-2"><img :src="g.image" loading="lazy" alt="" class="w-full h-full object-cover !rounded-none !border-0 !p-0"></div>
                    <span x-show="g.status" class="absolute top-2 left-2 chip bg-bg/90 text-white shadow" x-text="({playing:'Jugando',backlog:'Pendiente',completed:'Completado',platinum:'Platinado',abandoned:'Abandonado'})[g.status]"></span>
                    <div class="p-2.5">
                        <p class="text-sm font-bold leading-tight line-clamp-2 min-h-[2.2em]" x-text="g.name"></p>
                        <p class="text-xs text-muted mt-1" x-text="[g.year, g.genres.join(', ')].filter(Boolean).join(' · ')"></p>
                    </div>
                </a>
            </template>
        </div>

        <p x-show="buscado && !items.length && !cargandoRemoto" x-cloak class="text-center text-muted mt-10">Sin resultados para «<span x-text="q"></span>».</p>
        <p x-show="!buscado && q.length < 2 && !activos" class="text-center text-muted mt-10">Escribe al menos 2 letras o elige un filtro. Los resultados guardados salen al instante.</p>
    </div>
</x-layout>
