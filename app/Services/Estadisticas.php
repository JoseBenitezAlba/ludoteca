<?php

namespace App\Services;

use App\Models\Entry;
use App\Models\User;

/** Estadisticas de la biblioteca de un usuario. Todo gratis y visible. */
class Estadisticas
{
    /** Entradas que componen un número de las estadísticas. @return array{0: \Illuminate\Support\Collection, 1: string} */
    public function detalle(User $user, string $tipo, string $valor): array
    {
        $entries = $user->entries()->with('game')->get();
        $cerrados = ['playing', 'completed', 'platinum', 'abandoned'];

        [$lista, $titulo] = match ($tipo) {
            'estado' => [$entries->where('status', $valor), 'Estado: '.(Entry::STATUSES[$valor] ?? $valor)],
            'genero' => [$entries->whereIn('status', $cerrados)->filter(fn ($e) => in_array($valor, (array) $e->game->genres, true)), 'Género: '.$valor],
            'nota' => [$entries->where('rating', (int) $valor), 'Nota: '.rtrim(rtrim(number_format((int) $valor / 2, 1, ',', ''), '0'), ',').' estrellas'],
            'plataforma' => [$entries->where('platform', $valor), 'Plataforma: '.$valor],
            'anio' => [$entries->whereIn('status', ['completed', 'platinum'])->filter(fn ($e) => $e->finished_at && $e->finished_at->year === (int) $valor), 'Terminados en '.(int) $valor],
            default => [$entries->filter(fn ($e) => $this->horasDe($e) > 0)->sortByDesc(fn ($e) => $this->horasDe($e)), 'Horas por juego'],
        };

        return [$lista->values(), $titulo];
    }

    /** Horas del usuario o, si no las puso, la duración media del juego (solo si ya lo ha jugado). */
    public function horasDe(Entry $e): float
    {
        if ($e->hours) {
            return (float) $e->hours;
        }

        return in_array($e->status, ['completed', 'platinum', 'abandoned', 'playing'], true) ? (float) $e->game->playtime : 0.0;
    }

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
            'top_horas' => $entries->filter(fn ($e) => $this->horasDe($e) > 0)->sortByDesc(fn ($e) => $this->horasDe($e))->take(6)->values(),
            'favoritos' => $entries->where('favorite', true)->take(8)->values(),
        ];
    }
}
