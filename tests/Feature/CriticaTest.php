<?php

namespace Tests\Feature;

use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CriticaTest extends TestCase
{
    use RefreshDatabase;

    private function juego(string $nombre, array $extra = []): Game
    {
        static $n = 0;
        $n++;

        return Game::create($extra + ['rawg_id' => $n, 'slug' => 'j'.$n, 'name' => $nombre, 'released' => '2025-05-01']);
    }

    public function test_orden_por_critica_ignora_usuarios(): void
    {
        $this->juego('Popular', ['rawg_rating' => 5, 'ratings_count' => 9000]);
        $this->juego('Metacritic', ['metacritic' => 80, 'rawg_rating' => 3]);
        $this->juego('OpenCritic', ['opencritic_score' => 88, 'rawg_rating' => 2]);

        $r = $this->getJson('/api/buscar?q=&src=local&o=valoracion&g=&p=&mc=0')->json('items');
        $this->assertSame(['OpenCritic', 'Metacritic', 'Popular'], array_column($r ?? [], 'name'));
    }

    public function test_ficha_consulta_opencritic_una_vez(): void
    {
        config(['services.opencritic.key' => 'k']);
        Http::fake([
            '*/game/search*' => Http::response([['id' => 7, 'name' => 'Juego Nuevo', 'dist' => 0]]),
            '*/game/7' => Http::response(['topCriticScore' => 86.6, 'url' => 'https://opencritic.com/game/7/x']),
        ]);
        $g = $this->juego('Juego Nuevo');
        $this->get('/juegos/'.$g->slug)->assertOk()->assertSee('OpenCritic')->assertSee('87');
        $this->get('/juegos/'.$g->slug)->assertOk();
        $this->assertSame(87, $g->fresh()->opencritic_score);
        Http::assertSentCount(3); // 1 de RAWG (detalle) + 2 de OpenCritic
        Http::assertSent(fn ($r) => str_contains($r->url(), 'game/7'));
    }

    public function test_no_consulta_juegos_sin_lanzar(): void
    {
        config(['services.opencritic.key' => 'k']);
        Http::fake();
        $g = $this->juego('Futuro', ['released' => now()->addMonth()->toDateString()]);
        $this->get('/juegos/'.$g->slug)->assertOk();
        Http::assertNotSent(fn ($r) => str_contains($r->url(), 'opencritic'));
    }
}
