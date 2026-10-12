<?php

namespace App\Services;

use App\Models\Entry;
use App\Models\Game;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * "Sorpréndeme": juegos que no tienes, de los géneros que más juegas y mejor puntúas.
 * Sin cuenta o sin biblioteca, sugiere juegos populares y bien valorados.
 */
class Recomendador
{
    public function __construct(private Catalogo $catalogo) {}

    /**
     * @return array{0: Collection<int, Game>, 1: array<int, string>} juegos y motivo por id
     */
    public function sugerir(?User $user, int $n = 6): array
    {
        $perfil = $user ? $this->perfil($user) : [];
        $propios = $user ? Entry::where('user_id', $user->id)->pluck('game_id')->all() : [];
        $hoy = now()->toDateString();

        $generos = array_keys($perfil);
        $top = array_slice($generos, 0, 3);
        shuffle($top);
        $elegidos = array_slice($top, 0, 2); // 1-2 géneros por tanda, para que cambie cada vez

        $candidatos = collect();
        $remoto = $this->remoto($elegidos);
        $candidatos = $candidatos->concat($remoto);

        $local = Game::whereNotNull('image')->whereDate('released', '<=', $hoy)
            ->where('ratings_count', '>=', 200)->where('rawg_rating', '>=', 3.6)
            ->when($propios, fn ($q) => $q->whereNotIn('id', $propios))
            ->inRandomOrder()->limit(60)->get();
        if ($elegidos) {
            $local = $local->filter(fn ($g) => array_intersect($this->nombres($elegidos), (array) $g->genres));
        }
        $candidatos = $candidatos->concat($local);

        $juegos = $candidatos->unique('id')
            ->reject(fn ($g) => in_array($g->id, $propios, true) || $g->sinLanzar())
            ->shuffle()->take($n)->values();

        $motivos = [];
        foreach ($juegos as $g) {
            $motivos[$g->id] = $this->motivo($g, $elegidos ?: [], (bool) $perfil);
        }

        return [$juegos, $motivos];
    }

    /** genero (nombre RAWG en inglés) => peso, de mayor a menor */
    private function perfil(User $user): array
    {
        $pesos = [];
        foreach ($user->entries()->with('game')->get() as $e) {
            $p = match (true) {
                $e->status === 'abandoned' => -1,
                $e->rating !== null && $e->rating >= 8 => 3,
                $e->rating !== null && $e->rating >= 6 => 2,
                $e->rating !== null && $e->rating <= 4 => -1,
                in_array($e->status, ['completed', 'platinum', 'playing'], true) => 1.5,
                default => 0.5, // en pendientes: interés suave
            };
            foreach ((array) $e->game?->genres as $gn) {
                if ($this->slug($gn)) {
                    $pesos[$gn] = ($pesos[$gn] ?? 0) + $p;
                }
            }
        }
        $pesos = array_filter($pesos, fn ($v) => $v > 0);
        arsort($pesos);

        return $pesos;
    }

    private function remoto(array $generos): Collection
    {
        if (! config('services.rawg.key')) {
            return collect();
        }
        $slugs = array_values(array_filter(array_map(fn ($g) => $this->slug($g), $generos)));
        $pagina = random_int(1, 4);
        $clave = 'sorprende:'.now()->toDateString().':'.implode(',', $slugs).':'.$pagina;
        $items = Cache::get($clave);
        if ($items === null) {
            try {
                $params = [
                    'key' => config('services.rawg.key'),
                    'ordering' => '-added',
                    'dates' => '2005-01-01,'.now()->toDateString(),
                    'page_size' => 30,
                    'page' => $pagina,
                ];
                if ($slugs) {
                    $params['genres'] = implode(',', $slugs);
                }
                $r = Http::timeout(6)->connectTimeout(3)->get('https://api.rawg.io/api/games', $params);
                if (! $r->successful()) {
                    return collect();
                }
                $items = $r->json('results') ?? [];
                Cache::put($clave, $items, now()->addHours(12));
            } catch (\Throwable) {
                return collect();
            }
        }

        return collect($items)
            ->filter(fn ($g) => ! empty($g['background_image']) && ($g['rating'] ?? 0) >= 3.6 && ($g['ratings_count'] ?? 0) >= 150)
            ->map(fn ($g) => $this->catalogo->guardar($g));
    }

    private function motivo(Game $g, array $elegidos, bool $conPerfil): string
    {
        $es = array_combine(array_map(fn ($v) => $v[0], Catalogo::GENEROS), array_keys(Catalogo::GENEROS));
        $comunes = array_values(array_intersect((array) $g->genres, $elegidos));
        if ($conPerfil && $comunes) {
            return 'Por lo que juegas: '.($es[$comunes[0]] ?? $comunes[0]) === 'RPG' ? 'RPG' : mb_strtolower($es[$comunes[0]] ?? $comunes[0]);
        }

        return $g->rawg_rating && $g->rawg_rating >= 4 ? 'Muy bien valorado' : 'Popular';
    }

    private function slug(string $nombre): ?string
    {
        foreach (Catalogo::GENEROS as [$en, $slug]) {
            if ($en === $nombre) {
                return $slug;
            }
        }

        return null;
    }

    private function nombres(array $generos): array
    {
        return array_values($generos);
    }
}
