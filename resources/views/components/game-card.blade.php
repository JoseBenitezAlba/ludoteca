@props(['game', 'status' => null, 'stars' => null, 'wide' => false])
<a href="{{ route('juego', $game) }}" {{ $attributes->merge(['class' => 'group block rounded-2xl overflow-hidden bg-surface border border-line relative '.($wide ? 'w-44 shrink-0 snap-start' : '')]) }}>
    <div class="aspect-[16/11] bg-surface-2 overflow-hidden">
        @if ($game->image)<img src="{{ $game->thumb(500, 340) }}" alt="" loading="lazy" class="w-full h-full object-cover rounded-none border-0 p-0 group-active:scale-105 transition">@endif
    </div>
    @if ($status)<x-estado :status="$status" class="absolute top-2 left-2 backdrop-blur !bg-bg/90 shadow" />@endif
    <div class="p-2.5">
        <p class="text-sm font-bold leading-tight line-clamp-2 min-h-[2.2em]">{{ $game->name }}</p>
        <div class="mt-1 flex items-center justify-between text-xs text-muted">
            <span>{{ $game->year() }}</span>
            @if ($stars)<x-stars :value="$stars" size="text-sm" />@endif
        </div>
    </div>
</a>
