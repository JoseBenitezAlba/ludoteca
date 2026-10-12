@props(['title' => null, 'nav' => null])
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $title ? $title.' · ' : '' }}{{ config('app.name') }}</title>
    <meta name="theme-color" content="#0b0d13">
    <meta name="description" content="Tu biblioteca de videojuegos: estados, notas, listas y estadísticas gratis.">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="icon" href="/icons/icon-192.png">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="pb-24 md:pb-10">
@php
    $tabs = [
        ['home', 'Inicio', '/', 'M3 11l9-8 9 8v9a1 1 0 0 1-1 1h-5v-6h-6v6H4a1 1 0 0 1-1-1z'],
        ['buscar', 'Buscar', '/buscar', 'M11 4a7 7 0 1 0 4.2 12.6l4.1 4.1 1.4-1.4-4.1-4.1A7 7 0 0 0 11 4z'],
        ['biblioteca', 'Biblioteca', '/biblioteca', 'M4 4h4v16H4zM10 4h4v16h-4zM16.5 5.2l3.9-1 3 15.6-3.9 1z'],
        ['perfil', 'Stats', '/perfil', 'M4 20V10h4v10zM10 20V4h4v16zM16 20v-7h4v7z'],
    ];
@endphp
<header class="sticky top-0 z-30 bg-bg/85 backdrop-blur border-b border-line">
    <div class="max-w-5xl mx-auto px-4 h-14 flex items-center gap-4">
        <a href="/" class="font-extrabold text-xl tracking-tight flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg grid place-items-center text-sm" style="background:linear-gradient(135deg,#8b5cf6,#22d3ee)">L</span>
            {{ config('app.name') }}
        </a>
        <nav class="hidden md:flex gap-1 ml-6 text-sm font-semibold">
            @foreach ($tabs as [$k, $label, $url])
                <a href="{{ $url }}" class="px-3 py-1.5 rounded-lg {{ $nav === $k ? 'bg-surface-2 text-white' : 'text-muted hover:text-white' }}">{{ $label }}</a>
            @endforeach
            @auth <a href="/listas" class="px-3 py-1.5 rounded-lg {{ $nav === 'listas' ? 'bg-surface-2 text-white' : 'text-muted hover:text-white' }}">Listas</a> @endauth
        </nav>
        <div class="ml-auto flex items-center gap-2">
            @auth
                <a href="{{ route('perfil.propio') }}" class="w-8 h-8 rounded-full grid place-items-center text-sm font-bold bg-surface-2 border border-line">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</a>
                <form method="post" action="{{ route('salir') }}">@csrf<button class="text-xs text-muted hover:text-white px-2">Salir</button></form>
            @else
                <a href="{{ route('login') }}" class="text-sm font-semibold text-muted hover:text-white">Entrar</a>
                <a href="{{ route('registro') }}" class="btn-primary !py-1.5">Crear cuenta</a>
            @endauth
        </div>
    </div>
</header>

<main class="max-w-5xl mx-auto px-4 py-5">
    @if (session('ok'))
        <div x-data="{ s: true }" x-show="s" x-init="setTimeout(() => s = false, 3500)" x-transition class="mb-4 rounded-xl px-4 py-3 text-sm font-semibold bg-completed/15 text-completed border border-completed/30">{{ session('ok') }}</div>
    @endif
    {{ $slot }}
    <p class="mt-12 text-center text-xs text-muted">Catálogo de juegos por <a class="underline" href="https://rawg.io" target="_blank" rel="noopener">RAWG</a>. Estadísticas y medias, gratis para siempre.</p>
</main>

<nav class="md:hidden fixed bottom-0 inset-x-0 z-30 bg-surface/95 backdrop-blur border-t border-line safe-bottom">
    <div class="grid grid-cols-4">
        @foreach ($tabs as [$k, $label, $url, $path])
            <a href="{{ $url }}" class="flex flex-col items-center gap-0.5 py-2.5 text-[11px] font-semibold {{ $nav === $k ? 'text-white' : 'text-muted' }}">
                <svg viewBox="0 0 24 24" class="w-6 h-6 {{ $nav === $k ? 'text-accent' : '' }}" fill="currentColor"><path d="{{ $path }}"/></svg>
                {{ $label }}
            </a>
        @endforeach
    </div>
</nav>
</body>
</html>
