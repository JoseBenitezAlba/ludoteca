<x-layout title="Buscar" nav="buscar">
    <div x-data="buscador(@js(request('q', '')))">
        <input type="search" x-model="q" @input.debounce.150ms="lanzar()" placeholder="Busca un juego…" autofocus autocomplete="off" class="!rounded-2xl !py-3.5 mb-3">

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
        <p x-show="!buscado && q.length < 2" class="text-center text-muted mt-10">Escribe al menos 2 letras. Los resultados guardados salen al instante.</p>
    </div>
</x-layout>
