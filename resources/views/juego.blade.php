<x-layout :title="$game->name">
    <div class="-mx-4 -mt-5 mb-4 relative">
        <img src="{{ $game->thumb(900, 520) }}" alt="" class="w-full aspect-[16/10] md:aspect-[16/6] object-cover !rounded-none !border-0 !p-0">
        <div class="absolute inset-0" style="background:linear-gradient(to top, #0b0d13 5%, transparent 70%)"></div>
    </div>

    <h1 class="text-2xl md:text-4xl font-extrabold tracking-tight leading-tight">{{ $game->name }}</h1>
    <p class="text-sm text-muted mt-1">
        {{ collect([$game->released?->translatedFormat('j M Y'), implode(', ', array_slice($game->genres ?? [], 0, 3)), $game->playtime ? '~'.$game->playtime.' h' : null])->filter()->implode(' · ') }}
    </p>

    <div class="grid grid-cols-3 gap-2 mt-4 text-center">
        <div class="card py-3">
            @if ($comunidad['media'])<p class="text-2xl font-extrabold">{{ number_format($comunidad['media'], 1, ',', '') }}</p>
            @else<p class="text-2xl font-extrabold text-muted">–</p>@endif
            <p class="text-[11px] text-muted">Media de la app<br>{{ $comunidad['votos'] }} {{ $comunidad['votos'] === 1 ? 'voto' : 'votos' }}</p>
        </div>
        <div class="card py-3">
            <p class="text-2xl font-extrabold">{{ $game->rawg_rating ? number_format($game->rawg_rating, 1, ',', '') : '–' }}</p>
            <p class="text-[11px] text-muted">RAWG /5<br>{{ number_format($game->ratings_count, 0, ',', '.') }} votos</p>
        </div>
        <div class="card py-3">
            <p class="text-2xl font-extrabold">{{ $game->metacritic ?? '–' }}</p>
            <p class="text-[11px] text-muted">Metacritic<br>&nbsp;</p>
        </div>
    </div>

    @auth
    <form method="post" action="{{ route('juego.guardar', $game) }}" class="card p-4 mt-4 space-y-4"
          x-data="{ estado: @js(old('status', $entry->status ?? '')) }">
        @csrf
        <h2 class="h-section">{{ $entry ? 'En tu biblioteca' : 'Añadir a tu biblioteca' }}</h2>
        <div class="flex flex-wrap gap-2">
            @foreach (\App\Models\Entry::STATUSES as $k => $label)
                <label class="cursor-pointer">
                    <input type="radio" name="status" value="{{ $k }}" x-model="estado" class="sr-only peer" required>
                    <span class="chip !text-sm !px-3.5 !py-2 bg-surface-2 border border-line text-muted peer-checked:text-white peer-checked:border-accent peer-checked:bg-accent/25">{{ $label }}</span>
                </label>
            @endforeach
        </div>

        <div x-data="estrellas({{ $entry->rating ?? 0 }})" x-show="estado" x-cloak>
            <div class="flex items-center gap-3">
                <div class="flex">
                    @for ($i = 1; $i <= 5; $i++)
                        <div class="relative w-9 h-9 text-3xl leading-9 select-none">
                            <span class="absolute inset-0 text-center text-[#323a52]">★</span>
                            <span class="absolute inset-0 text-center text-platinum overflow-hidden" :style="'width:' + (v >= {{ $i * 2 }} ? 100 : (v === {{ $i * 2 - 1 }} ? 50 : 0)) + '%'"><span class="block w-9 text-center">★</span></span>
                            <button type="button" class="absolute inset-y-0 left-0 w-1/2" @click="poner({{ $i * 2 - 1 }})" aria-label="{{ $i - 0.5 }} estrellas"></button>
                            <button type="button" class="absolute inset-y-0 right-0 w-1/2" @click="poner({{ $i * 2 }})" aria-label="{{ $i }} estrellas"></button>
                        </div>
                    @endfor
                </div>
                <span class="text-sm text-muted" x-text="texto"></span>
            </div>
            <input type="hidden" name="rating" :value="v || ''">
        </div>

        <div class="grid grid-cols-2 gap-3" x-show="estado" x-cloak>
            <label class="text-xs text-muted">Horas jugadas<input type="number" step="0.5" min="0" name="hours" value="{{ old('hours', $entry->hours ?? '') }}" placeholder="{{ $game->playtime ? '~'.$game->playtime : '0' }}"></label>
            <label class="text-xs text-muted">Plataforma
                <select name="platform">
                    <option value="">–</option>
                    @foreach (collect(['PC', 'PS5', 'Xbox', 'Switch', 'Móvil'])->merge(array_slice($game->platforms ?? [], 0, 6))->unique() as $p)
                        <option @selected(old('platform', $entry->platform ?? '') === $p)>{{ $p }}</option>
                    @endforeach
                </select>
            </label>
            <label class="text-xs text-muted col-span-2" x-show="['completed','platinum'].includes(estado)">Fecha en que lo terminaste
                <input type="date" name="finished_at" value="{{ old('finished_at', optional($entry?->finished_at)->format('Y-m-d')) }}">
            </label>
        </div>
        <div x-show="estado" x-cloak class="space-y-3">
            <textarea name="review" rows="3" maxlength="2000" placeholder="Tu opinión (opcional)">{{ old('review', $entry->review ?? '') }}</textarea>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="favorite" value="1" @checked(old('favorite', $entry->favorite ?? false)) class="!w-5 !h-5 accent-accent"> Marcar como favorito</label>
        </div>
        <div class="flex items-center gap-3">
            <button class="btn-primary flex-1" x-bind:disabled="!estado" x-bind:class="!estado && 'opacity-50'">Guardar</button>
        </div>
    </form>
    @if ($entry)
        <form method="post" action="{{ route('juego.quitar', $game) }}" class="mt-2 text-center" onsubmit="return confirm('¿Quitar de tu biblioteca?')">@csrf @method('DELETE')<button class="btn-danger">Quitar de mi biblioteca</button></form>
    @endif

    @if ($listas->isNotEmpty())
        <form method="post" action="{{ route('listas.agregar', $game) }}" class="card p-3 mt-3 flex gap-2">
            @csrf
            <select name="lista" class="!w-auto flex-1">@foreach ($listas as $l)<option value="{{ $l->id }}">{{ $l->name }}</option>@endforeach</select>
            <button class="btn-ghost shrink-0">+ Lista</button>
        </form>
    @endif
    @else
        <div class="card p-4 mt-4 text-center">
            <p class="font-semibold">Entra para guardar este juego y ponerle nota.</p>
            <div class="mt-3 flex gap-2 justify-center"><a href="{{ route('registro') }}" class="btn-primary">Crear cuenta</a><a href="{{ route('login') }}" class="btn-ghost">Entrar</a></div>
        </div>
    @endauth

    @if ($game->description)
        <section class="mt-6" x-data="{ mas: false }">
            <h2 class="h-section mb-2">Sobre el juego</h2>
            <p class="text-sm text-muted leading-relaxed whitespace-pre-line" :class="!mas && 'line-clamp-5'">{{ $game->description }}</p>
            <button class="text-sm text-accent mt-1" @click="mas = !mas" x-text="mas ? 'Ver menos' : 'Ver más'"></button>
        </section>
    @endif

    @if ($resenas->isNotEmpty())
        <section class="mt-6">
            <h2 class="h-section mb-2">Opiniones</h2>
            <div class="space-y-2">
                @foreach ($resenas as $r)
                    <div class="card p-3">
                        <div class="flex items-center justify-between text-sm"><a href="{{ route('perfil', $r->user) }}" class="font-bold">{{ $r->user->name }}</a><x-stars :value="$r->stars() ?? 0" size="text-sm" /></div>
                        <p class="text-sm text-muted mt-1">{{ $r->review }}</p>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if ($game->developers || $game->platforms)
        <p class="mt-6 text-xs text-muted">{{ $game->developers ? 'Desarrolla: '.implode(', ', $game->developers).'. ' : '' }}{{ $game->platforms ? 'Plataformas: '.implode(', ', array_slice($game->platforms, 0, 8)).'.' : '' }}</p>
    @endif
</x-layout>
