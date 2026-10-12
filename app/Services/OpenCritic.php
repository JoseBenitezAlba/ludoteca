<?php

namespace App\Services;

use App\Models\Game;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Nota de la crítica de OpenCritic (plan gratis de RapidAPI: 25 búsquedas al día).
 * Se busca una sola vez por juego y se guarda en la base de datos.
 */
class OpenCritic
{
    private const HOST = 'opencritic-api.p.rapidapi.com';

    private const LIMITE_DIA = 24;      // la API deja 25, dejamos margen
    private const LIMITE_RELLENO = 18;  // lo que puede gastar el relleno automático; el resto queda para fichas

    public function activo(): bool
    {
        return (bool) config('services.opencritic.key');
    }

    /** Búsquedas gastadas hoy (cada juego comprobado hoy cuenta una). */
    public function usadasHoy(): int
    {
        return Game::where('opencritic_checked_at', '>=', now()->startOfDay())->count();
    }

    /** Búsquedas que dice la API que quedan hoy (la última cifra conocida; 99 si aún no la sabemos). */
    private function restantes(): int
    {
        return (int) Cache::get('opencritic-restantes', 99);
    }

    /** Juegos que merece la pena consultar: ya han salido y no tienen Metacritic. */
    public function pendiente(Game $g): bool
    {
        return $this->activo()
            && $g->metacritic === null
            && $g->opencritic_checked_at === null
            && $g->released !== null
            && ! $g->released->isFuture();
    }

    /** Para la ficha: consulta si queda cupo hoy. */
    public function completar(Game $g): void
    {
        if ($this->pendiente($g) && $this->usadasHoy() < self::LIMITE_DIA && $this->restantes() > 1) {
            $this->consultar($g);
        }
    }

    /** Relleno automático: el juego reciente más popular que falte, si queda cupo de relleno. */
    public function rellenarUno(): void
    {
        if (! $this->activo() || $this->usadasHoy() >= self::LIMITE_RELLENO || $this->restantes() <= 8) {
            return;
        }
        $lock = Cache::lock('opencritic-relleno', 30);
        if (! $lock->get()) {
            return;
        }
        try {
            $g = Game::whereNull('metacritic')->whereNull('opencritic_checked_at')
                ->whereDate('released', '<=', now()->toDateString())
                ->orderByRaw('case when released >= ? then 0 else 1 end', ['2024-01-01'])
                ->orderByDesc('ratings_count')
                ->first();
            if ($g) {
                $this->consultar($g);
            }
        } finally {
            $lock->release();
        }
    }

    public function consultar(Game $g): void
    {
        if (Cache::get('opencritic-pausa')) {
            return;
        }
        try {
            $cab = ['X-RapidAPI-Key' => config('services.opencritic.key'), 'X-RapidAPI-Host' => self::HOST];
            $r = Http::withHeaders($cab)->timeout(8)->connectTimeout(3)
                ->get('https://'.self::HOST.'/game/search', ['criteria' => $g->name]);
            if ($r->status() === 429) {
                Cache::put('opencritic-pausa', 1, now()->endOfDay());

                return;
            }
            if ($r->header('x-ratelimit-searches-remaining') !== '') {
                $seg = max(60, (int) $r->header('x-ratelimit-searches-reset'));
                Cache::put('opencritic-restantes', (int) $r->header('x-ratelimit-searches-remaining'), now()->addSeconds($seg));
            }
            if (! $r->successful()) {
                return; // error temporal: no lo marcamos como comprobado
            }
            $id = null;
            foreach ($r->json() ?? [] as $c) {
                if (($c['dist'] ?? 1) <= 0.02 || $this->norm($c['name'] ?? '') === $this->norm($g->name)) {
                    $id = $c['id'];
                    break;
                }
            }
            $score = null;
            $url = null;
            if ($id) {
                $d = Http::withHeaders($cab)->timeout(8)->connectTimeout(3)->get('https://'.self::HOST.'/game/'.$id);
                if (! $d->successful()) {
                    return;
                }
                $s = $d->json('topCriticScore');
                if (is_numeric($s) && $s > 0) {
                    $score = (int) round($s);
                    $url = $d->json('url');
                }
            }
            $g->forceFill([
                'opencritic_score' => $score,
                'opencritic_url' => $url,
                'opencritic_checked_at' => now(),
            ])->save();
        } catch (\Throwable $e) {
            Log::info('OpenCritic no responde: '.$e->getMessage());
        }
    }

    private function norm(string $s): string
    {
        return preg_replace('/[^a-z0-9]+/', '', strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $s) ?: $s));
    }
}
