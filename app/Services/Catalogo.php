<?php

namespace App\Services;

use App\Models\Game;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Catalogo de juegos. La busqueda local (base de datos) responde al instante y
 * la de RAWG se pide aparte, con timeout corto y cache, para que nunca bloquee.
 */
class Catalogo
{
    private const BASE = 'https://api.rawg.io/api';

    public function buscarLocal(string $q, int $limit = 24): Collection
    {
        $q = $this->normalizar($q);
        if ($q === '') {
            return collect();
        }

        $palabras = array_filter(explode(' ', $q));
        $query = Game::query();
        foreach ($palabras as $palabra) {
            $query->whereRaw('lower(name) like ?', ['%'.$palabra.'%']);
        }

        return $query
            ->orderByDesc('ratings_count')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Game>|null null si RAWG no responde
     */
    public function buscarRemoto(string $q, int $limit = 20): ?Collection
    {
        $q = $this->normalizar($q);
        if (mb_strlen($q) < 2 || ! config('services.rawg.key')) {
            return null;
        }

        $resultados = Cache::get('rawg:buscar:'.md5($q));
        if ($resultados === null) {
            try {
                $resp = Http::timeout(4)->connectTimeout(2)->get(self::BASE.'/games', [
                    'key' => config('services.rawg.key'),
                    'search' => $q,
                    'page_size' => $limit,
                    'search_precise' => 'true',
                ]);
                if (! $resp->successful()) {
                    return null;
                }
                $resultados = $resp->json('results') ?? [];
                Cache::put('rawg:buscar:'.md5($q), $resultados, now()->addDay());
            } catch (\Throwable $e) {
                Log::info('RAWG no responde: '.$e->getMessage());

                return null;
            }
        }

        return collect($resultados)
            ->filter(fn ($g) => ! empty($g['background_image']))
            ->map(fn ($g) => $this->guardar($g))
            ->values();
    }

    /** Completa descripcion y desarrolladores la primera vez que se abre un juego. */
    public function detallar(Game $game): Game
    {
        if ($game->detailed_at || ! config('services.rawg.key')) {
            return $game;
        }

        try {
            $resp = Http::timeout(4)->connectTimeout(2)->get(self::BASE.'/games/'.$game->rawg_id, [
                'key' => config('services.rawg.key'),
            ]);
            if ($resp->successful()) {
                $d = $resp->json();
                $game->update([
                    'description' => $d['description_raw'] ?? null,
                    'developers' => collect($d['developers'] ?? [])->pluck('name')->all(),
                    'playtime' => $d['playtime'] ?: $game->playtime,
                    'detailed_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::info('RAWG detalle no disponible: '.$e->getMessage());
        }

        return $game;
    }

    public function guardar(array $g): Game
    {
        return Game::updateOrCreate(['rawg_id' => $g['id']], [
            'slug' => $g['slug'],
            'name' => $g['name'],
            'image' => $g['background_image'] ?? null,
            'released' => $g['released'] ?? null,
            'genres' => collect($g['genres'] ?? [])->pluck('name')->all(),
            'platforms' => collect($g['platforms'] ?? [])->pluck('platform.name')->all(),
            'metacritic' => $g['metacritic'] ?? null,
            'rawg_rating' => $g['rating'] ?? null,
            'ratings_count' => $g['ratings_count'] ?? 0,
            'playtime' => $g['playtime'] ?? null,
        ]);
    }

    private function normalizar(string $q): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/', ' ', $q)));
    }
}
