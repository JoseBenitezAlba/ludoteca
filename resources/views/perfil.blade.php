@php
    $colores = ['playing' => '#22d3ee', 'backlog' => '#94a3b8', 'completed' => '#34d399', 'platinum' => '#fbbf24', 'abandoned' => '#fb7185'];
    $maxDist = max(1, max($stats['distribucion']));
    $maxGen = max(1, max($stats['generos'] ?: [1]));
    $maxAnio = max(1, max($stats['por_anio'] ?: [1]));
    $propio = auth()->id() === $user->id;
@endphp
<x-layout :title="$user->name" nav="perfil">
    <div class="flex items-center gap-4 mb-5">
        <div class="w-16 h-16 rounded-2xl grid place-items-center text-2xl font-extrabold" style="background:linear-gradient(135deg,#8b5cf6,#22d3ee)">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</div>
        <div>
            <h1 class="text-2xl font-extrabold tracking-tight">{{ $user->name }}</h1>
            <p class="text-sm text-muted">@<span>{{ $user->username }}</span>{{ $user->bio ? ' · '.$user->bio : '' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
        <div class="card p-4"><p class="text-3xl font-extrabold">{{ $stats['total'] }}</p><p class="text-xs text-muted">Juegos</p></div>
        <a href="{{ route('perfil.detalle', [$user, 'tipo' => 'horas']) }}" class="card p-4 block"><p class="text-3xl font-extrabold">{{ number_format($stats['horas'], 0, ',', '.') }}<span class="text-base text-muted"> h</span></p><p class="text-xs text-muted">Horas{{ $stats['horas_estimadas'] ? ' (algunas estimadas)' : '' }} ›</p></a>
        <div class="card p-4"><p class="text-3xl font-extrabold">{{ $stats['media'] ? number_format($stats['media'], 2, ',', '') : '–' }}</p><p class="text-xs text-muted">Nota media /5 · {{ $stats['valoradas'] }} valorados</p></div>
        <div class="card p-4"><p class="text-3xl font-extrabold">{{ $stats['tasa_final'] !== null ? $stats['tasa_final'].'%' : '–' }}</p><p class="text-xs text-muted">Los termina (vs. abandona)</p></div>
    </div>

    @if ($stats['total'])
    <section class="card p-4 mb-4">
        <h2 class="h-section mb-3">Estados</h2>
        <div class="flex h-3 rounded-full overflow-hidden bg-surface-2 mb-3">
            @foreach ($stats['por_estado'] as $k => $n)
                @if ($n)<div style="width: {{ $n / $stats['total'] * 100 }}%; background: {{ $colores[$k] }}"></div>@endif
            @endforeach
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 text-sm">
            @foreach (\App\Models\Entry::STATUSES as $k => $label)
                <a href="{{ route('perfil.detalle', [$user, 'tipo' => 'estado', 'valor' => $k]) }}" class="flex items-center gap-2"><span class="w-2.5 h-2.5 rounded-full" style="background: {{ $colores[$k] }}"></span>{{ $label }} <b>{{ $stats['por_estado'][$k] }}</b></a>
            @endforeach
        </div>
    </section>

    <div class="grid md:grid-cols-2 gap-4 mb-4">
        <section class="card p-4">
            <h2 class="h-section mb-3">Distribución de notas</h2>
            <div class="flex items-end gap-1.5 h-32">
                @foreach ($stats['distribucion'] as $nota => $n)
                    <a @if ($n) href="{{ route('perfil.detalle', [$user, 'tipo' => 'nota', 'valor' => $nota]) }}" @endif class="flex-1 flex flex-col items-center justify-end h-full">
                        <span class="text-[10px] text-muted mb-1">{{ $n ?: '' }}</span>
                        <div class="w-full rounded-t-md" style="height: {{ $n ? max(6, $n / $maxDist * 100) : 3 }}%; background: {{ $n ? 'linear-gradient(to top,#6366f1,#a78bfa)' : '#272d40' }}"></div>
                    </a>
                @endforeach
            </div>
            <div class="flex gap-1.5 mt-1">@foreach ($stats['distribucion'] as $nota => $n)<span class="flex-1 text-center text-[10px] text-muted">{{ $nota % 2 ? '' : $nota / 2 }}</span>@endforeach</div>
            <p class="text-[11px] text-muted mt-1 text-center">estrellas</p>
        </section>

        <section class="card p-4">
            <h2 class="h-section mb-3">Géneros más jugados</h2>
            <div class="space-y-2">
                @foreach ($stats['generos'] as $g => $n)
                    <a href="{{ route('perfil.detalle', [$user, 'tipo' => 'genero', 'valor' => $g]) }}" class="block">
                        <div class="flex justify-between text-sm mb-1"><span>{{ $g }}</span><span class="text-muted">{{ $n }} ›</span></div>
                        <div class="h-2 rounded-full bg-surface-2"><div class="h-2 rounded-full" style="width: {{ $n / $maxGen * 100 }}%; background: linear-gradient(90deg,#8b5cf6,#22d3ee)"></div></div>
                    </a>
                @endforeach
            </div>
        </section>
    </div>

    <div class="grid md:grid-cols-2 gap-4 mb-4">
        @if ($stats['por_anio'])
        <section class="card p-4">
            <h2 class="h-section mb-3">Juegos terminados por año</h2>
            <div class="space-y-2">
                @foreach ($stats['por_anio'] as $anio => $n)
                    <a href="{{ route('perfil.detalle', [$user, 'tipo' => 'anio', 'valor' => $anio]) }}" class="flex items-center gap-3 text-sm"><span class="w-10 text-muted">{{ $anio }}</span><div class="flex-1 h-5 rounded-md bg-surface-2"><div class="h-5 rounded-md bg-completed/80 text-[11px] text-bg font-bold pl-2 leading-5" style="width: {{ max(8, $n / $maxAnio * 100) }}%">{{ $n }}</div></div></a>
                @endforeach
            </div>
        </section>
        @endif
        @if ($stats['plataformas'])
        <section class="card p-4">
            <h2 class="h-section mb-3">Plataformas</h2>
            <div class="flex flex-wrap gap-2">@foreach ($stats['plataformas'] as $p => $n)<a href="{{ route('perfil.detalle', [$user, 'tipo' => 'plataforma', 'valor' => $p]) }}" class="chip !text-sm bg-surface-2 border border-line">{{ $p }} <b class="ml-1.5">{{ $n }}</b></a>@endforeach</div>
        </section>
        @endif
    </div>

    @if ($stats['top_horas']->isNotEmpty())
    <section class="card p-4 mb-4">
        <div class="flex justify-between items-baseline mb-3"><h2 class="h-section">Horas por juego</h2><a href="{{ route('perfil.detalle', [$user, 'tipo' => 'horas']) }}" class="text-xs text-accent">Ver todos ›</a></div>
        <div class="space-y-2">
            @foreach ($stats['top_horas'] as $e)
                <a href="{{ route('juego', $e->game) }}" class="flex items-center justify-between text-sm"><span class="truncate pr-3">{{ $e->game->name }}</span><span class="text-muted shrink-0">{{ rtrim(rtrim(number_format($e->hours ?: $e->game->playtime, 1, ',', ''), '0'), ',') }} h{{ $e->hours ? '' : ' (est.)' }}</span></a>
            @endforeach
        </div>
    </section>
    @endif

    @if ($stats['mejores']->isNotEmpty())
    <section class="mb-4">
        <h2 class="h-section mb-3">Sus mejores notas</h2>
        <div class="scroll-x">@foreach ($stats['mejores'] as $e)<x-game-card :game="$e->game" :wide="true" :stars="$e->stars()" />@endforeach</div>
    </section>
    @endif
    @if ($stats['favoritos']->isNotEmpty())
    <section class="mb-4">
        <h2 class="h-section mb-3">Favoritos</h2>
        <div class="scroll-x">@foreach ($stats['favoritos'] as $e)<x-game-card :game="$e->game" :wide="true" :stars="$e->stars()" />@endforeach</div>
    </section>
    @endif
    @else
        <div class="card p-8 text-center"><p class="font-semibold">Aún no hay estadísticas.</p><p class="text-sm text-muted mt-1">En cuanto añadas juegos y los puntúes, aparecen aquí.</p><a href="{{ route('buscar') }}" class="btn-primary mt-3">Buscar juegos</a></div>
    @endif

    @if ($listas->isNotEmpty())
    <section class="mb-4">
        <h2 class="h-section mb-3">Listas</h2>
        <div class="space-y-2">@foreach ($listas as $l)<a href="{{ route('listas.show', $l) }}" class="card p-3 flex items-center justify-between"><span class="font-bold">{{ $l->name }}</span><span class="text-sm text-muted">{{ $l->games_count }} juegos</span></a>@endforeach</div>
    </section>
    @endif
</x-layout>
