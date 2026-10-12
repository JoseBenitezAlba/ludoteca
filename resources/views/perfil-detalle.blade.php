<x-layout :title="$titulo" nav="perfil">
    <a href="{{ route('perfil', $user) }}" class="text-sm text-muted">‹ Estadísticas de {{ $user->name }}</a>
    <h1 class="text-2xl font-extrabold mt-1">{{ $titulo }}</h1>
    <p class="text-sm text-muted mb-4">{{ $entries->count() }} {{ $entries->count() === 1 ? 'juego' : 'juegos' }}@if ($horas > 0) · {{ number_format($horas, 0, ',', '.') }} h @endif</p>

    @if ($entries->isEmpty())
        <p class="text-center text-muted mt-10">No hay juegos aquí.</p>
    @else
        <div class="space-y-2">
            @foreach ($entries as $e)
                @php $h = $est->horasDe($e); @endphp
                <a href="{{ route('juego', $e->game) }}" class="card p-2 flex items-center gap-3">
                    <div class="w-24 aspect-[16/11] rounded-lg overflow-hidden bg-surface-2 shrink-0">@if ($e->game->image)<img src="{{ $e->game->thumb(300, 200) }}" alt="" loading="lazy" class="w-full h-full object-cover !rounded-none !border-0 !p-0">@endif</div>
                    <div class="min-w-0 flex-1">
                        <p class="font-bold text-sm leading-tight line-clamp-2">{{ $e->game->name }}</p>
                        <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-muted">
                            <x-estado :status="$e->status" />
                            @if ($e->stars())<x-stars :value="$e->stars()" size="text-sm" />@endif
                            @if ($h > 0)<span>{{ rtrim(rtrim(number_format($h, 1, ',', ''), '0'), ',') }} h{{ $e->hours ? '' : ' (est.)' }}</span>@endif
                            @if ($e->platform)<span>{{ $e->platform }}</span>@endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</x-layout>
