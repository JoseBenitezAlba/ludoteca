<?php

namespace App\Services;

use App\Models\Entry;
use App\Models\User;

/** Estadisticas de la biblioteca de un usuario. Todo gratis y visible. */
class Estadisticas
{
    public function de(User $user): array
    {
        $entries = $user->entries()->with('game')->get();

        $porEstado = [];
        foreach (array_keys(Entry::STATUSES) as $s) {
            $porEstado[$s] = $entries->where('status', $s)->count();
        }

        $valoradas = $entries->whereNotNull('rating');
        $media = $valoradas->count() ? round($valoradas->avg('rating') / 2, 2) : null;

        $distribucion = [];
        for ($i = 1; $i <= 10; $i++) {
            $distribucion[$i] = $valoradas->where('rating', $i)->count();
        }

        // horas: las que ha puesto el usuario; si no, la duracion media del juego
        $horasReales = $entries->sum(fn ($e) => (float) $e->hours);
        $horasEstimadas = $entries
            ->filter(fn ($e) => ! $e->hours && in_array($e->status, ['completed', 'platinum', 'abandoned', 'playing']))
            ->sum(fn ($e) => (int) $e->game->playtime);

        $generos = [];
        foreach ($entries->whereIn('status', ['playing', 'completed', 'platinum', 'abandoned']) as $e) {
            foreach ($e->game->genres ?? [] as $g) {
                $generos[$g] = ($generos[$g] ?? 0) + 1;
            }
        }
        arsort($generos);

        $plataformas = [];
        foreach ($entries as $e) {
            if ($e->platform) {
                $plataformas[$e->platform] = ($plataformas[$e->platform] ?? 0) + 1;
            }
        }
        arsort($plataformas);

        $terminados = $porEstado['completed'] + $porEstado['platinum'];
        $cerrados = $terminados + $porEstado['abandoned'];

        $porAnio = $entries->whereIn('status', ['completed', 'platinum'])
            ->filter(fn ($e) => $e->finished_at)
            ->groupBy(fn ($e) => $e->finished_at->year)
            ->map->count()->sortKeys()->all();

        return [
            'total' => $entries->count(),
            'por_estado' => $porEstado,
            'media' => $media,
            'valoradas' => $valoradas->count(),
            'distribucion' => $distribucion,
            'horas' => (int) round($horasReales + $horasEstimadas),
            'horas_estimadas' => $horasEstimadas > 0,
            'generos' => array_slice($generos, 0, 8, true),
            'plataformas' => array_slice($plataformas, 0, 6, true),
            'tasa_final' => $cerrados ? (int) round($terminados / $cerrados * 100) : null,
            'por_anio' => $porAnio,
            'mejores' => $valoradas->sortByDesc('rating')->take(5)->values(),
            'favoritos' => $entries->where('favorite', true)->take(8)->values(),
        ];
    }
}
