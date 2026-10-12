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

    /** etiqueta => [nombre guardado en BD, slug RAWG] */
    public const GENEROS = [
        'Acción' => ['Action', 'action'],
        'Aventura' => ['Adventure', 'adventure'],
        'RPG' => ['RPG', 'role-playing-games-rpg'],
        'Estrategia' => ['Strategy', 'strategy'],
        'Shooter' => ['Shooter', 'shooter'],
        'Indie' => ['Indie', 'indie'],
        'Plataformas' => ['Platformer', 'platformer'],
        'Puzles' => ['Puzzle', 'puzzle'],
        'Carreras' => ['Racing', 'racing'],
        'Deportes' => ['Sports', 'sports'],
        'Lucha' => ['Fighting', 'fighting'],
        'Simulación' => ['Simulation', 'simulation'],
    ];

    /** etiqueta => [texto a buscar en BD, ids RAWG] */
    public const PLATAFORMAS = [
        'PC' => [['PC'], '4'],
        'PlayStation' => [['PlayStation'], '187,18'],
        'Xbox' => [['Xbox'], '1,186'],
        'Switch' => [['Nintendo Switch'], '7'],
        'Móvil' => [['iOS', 'Android'], '3,21'],
    ];

    /** Limpia los filtros que llegan de la URL. */
    public static function filtros(array $in): array
    {
        $gen = array_values(array_intersect((array) ($in['g'] ?? []), array_keys(self::GENEROS)));
        $plat = array_values(array_intersect((array) ($in['p'] ?? []), array_keys(self::PLATAFORMAS)));
        $anio = fn ($v) => is_numeric($v) && $v >= 1970 && $v <= 2100 ? (int) $v : null;
        $mc = is_numeric($in['mc'] ?? null) ? max(0, min(100, (int) $in['mc'])) : null;

        return [
            'g' => $gen,
            'p' => $plat,
            'desde' => $anio($in['desde'] ?? null),
            'hasta' => $anio($in['hasta'] ?? null),
            'mc' => $mc ?: null,
        ];
    }

    public static function hayFiltros(array $f): bool
    {
        return $f['g'] || $f['p'] || $f['desde'] || $f['hasta'] || $f['mc'];
    }

    public function buscarLocal(string $q, array $f = [], int $limit = 24): Collection
    {
        $q = $this->normalizar($q);
        $f = self::filtros($f);
        if ($q === '' && ! self::hayFiltros($f)) {
            return collect();
        }

        $palabras = array_filter(explode(' ', $q));
        $query = Game::query();
        foreach ($palabras as $palabra) {
            $query->whereRaw('lower(name) like ?', ['%'.$palabra.'%']);
        }
        if ($f['g']) {
            $query->where(function ($w) use ($f) {
                foreach ($f['g'] as $etq) {
                    $w->orWhereRaw('lower(cast(genres as text)) like ?', ['%"'.mb_strtolower(self::GENEROS[$etq][0]).'"%']);
                }
            });
        }
        if ($f['p']) {
            $query->where(function ($w) use ($f) {
                foreach ($f['p'] as $etq) {
                    foreach (self::PLATAFORMAS[$etq][0] as $txt) {
                        $w->orWhereRaw('lower(cast(platforms as text)) like ?', ['%'.mb_strtolower($txt).'%']);
                    }
                }
            });
        }
        if ($f['desde']) {
            $query->whereDate('released', '>=', $f['desde'].'-01-01');
        }
        if ($f['hasta']) {
            $query->whereDate('released', '<=', $f['hasta'].'-12-31');
        }
        if ($f['mc']) {
            $query->where('metacritic', '>=', $f['mc']);
        }

        return $query
            ->orderByDesc('ratings_count')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Game>|null null si RAWG no responde
     */
    public function buscarRemoto(string $q, array $f = [], int $limit = 20): ?Collection
    {
        $q = $this->normalizar($q);
        $f = self::filtros($f);
        if ((mb_strlen($q) < 2 && ! self::hayFiltros($f)) || ! config('services.rawg.key')) {
            return null;
        }

        $clave = 'rawg:buscar:'.md5($q.json_encode($f));
        $resultados = Cache::get($clave);
        if ($resultados === null) {
            try {
                $params = [
                    'key' => config('services.rawg.key'),
                    'page_size' => $limit,
                ];
                if ($q !== '') {
                    $params['search'] = $q;
                    $params['search_precise'] = 'true';
                } else {
                    $params['ordering'] = '-added';
                }
                if ($f['g']) {
                    $params['genres'] = implode(',', array_map(fn ($e) => self::GENEROS[$e][1], $f['g']));
                }
                if ($f['p']) {
                    $params['platforms'] = implode(',', array_map(fn ($e) => self::PLATAFORMAS[$e][1], $f['p']));
                }
                if ($f['desde'] || $f['hasta']) {
                    $params['dates'] = ($f['desde'] ?: 1970).'-01-01,'.($f['hasta'] ?: 2100).'-12-31';
                }
                if ($f['mc']) {
                    $params['metacritic'] = $f['mc'].',100';
                }
                $resp = Http::timeout(4)->connectTimeout(2)->get(self::BASE.'/games', $params);
                if (! $resp->successful()) {
                    return null;
                }
                $resultados = $resp->json('results') ?? [];
                Cache::put($clave, $resultados, now()->addDay());
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
