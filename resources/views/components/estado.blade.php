@props(['status'])
@php
    $c = ['playing' => 'text-playing bg-playing/15', 'backlog' => 'text-backlog bg-backlog/15', 'completed' => 'text-completed bg-completed/15', 'platinum' => 'text-platinum bg-platinum/15', 'abandoned' => 'text-abandoned bg-abandoned/15'][$status] ?? '';
@endphp
<span {{ $attributes->merge(['class' => "chip $c"]) }}>{{ \App\Models\Entry::STATUSES[$status] ?? $status }}</span>
