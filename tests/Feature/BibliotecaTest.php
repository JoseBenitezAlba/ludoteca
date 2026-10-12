<?php

namespace Tests\Feature;

use App\Models\Entry;
use App\Models\Game;
use App\Models\User;
use App\Services\Estadisticas;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BibliotecaTest extends TestCase
{
    use RefreshDatabase;

    private function juego(string $nombre, array $extra = []): Game
    {
        return Game::create(array_merge([
            'rawg_id' => random_int(1, 999999), 'slug' => str($nombre)->slug(), 'name' => $nombre,
            'genres' => ['RPG'], 'playtime' => 30,
        ], $extra));
    }

    public function test_busqueda_local_responde_sin_red(): void
    {
        Http::preventStrayRequests();
        $this->juego('The Witcher 3');
        $this->juego('Hades');

        $this->getJson('/api/buscar?src=local&q=witcher')
            ->assertOk()->assertJsonCount(1, 'items')->assertJsonPath('items.0.name', 'The Witcher 3');
    }

    public function test_si_rawg_falla_la_busqueda_remota_no_rompe(): void
    {
        config(['services.rawg.key' => 'x']);
        Http::fake(['api.rawg.io/*' => Http::response('error', 500)]);

        $this->getJson('/api/buscar?src=remoto&q=zelda')->assertOk()->assertJson(['ok' => false, 'items' => []]);
    }

    public function test_busqueda_remota_guarda_los_juegos_y_cachea(): void
    {
        config(['services.rawg.key' => 'x']);
        Http::fake(['api.rawg.io/*' => Http::response(['results' => [[
            'id' => 77, 'slug' => 'zelda-x', 'name' => 'Zelda X', 'background_image' => 'https://media.rawg.io/media/games/a/b.jpg',
            'released' => '2020-01-01', 'genres' => [['name' => 'Adventure']], 'platforms' => [], 'rating' => 4.2, 'ratings_count' => 10,
        ]]])]);

        $this->getJson('/api/buscar?src=remoto&q=zelda')->assertOk()->assertJsonPath('items.0.slug', 'zelda-x');
        $this->getJson('/api/buscar?src=remoto&q=zelda')->assertOk();

        Http::assertSentCount(1);
        $this->assertDatabaseHas('games', ['rawg_id' => 77]);
    }

    public function test_guardar_juego_con_estado_y_nota(): void
    {
        $user = User::factory()->create();
        $game = $this->juego('Hades');

        $this->actingAs($user)->post("/juegos/{$game->slug}/guardar", ['status' => 'completed', 'rating' => 9, 'hours' => 40])
            ->assertRedirect();

        $this->assertDatabaseHas('entries', ['user_id' => $user->id, 'game_id' => $game->id, 'status' => 'completed', 'rating' => 9]);
    }

    public function test_estadisticas_calculan_media_y_generos(): void
    {
        $user = User::factory()->create();
        $a = $this->juego('A', ['genres' => ['RPG', 'Action']]);
        $b = $this->juego('B', ['genres' => ['RPG']]);
        Entry::create(['user_id' => $user->id, 'game_id' => $a->id, 'status' => 'completed', 'rating' => 8, 'hours' => 10]);
        Entry::create(['user_id' => $user->id, 'game_id' => $b->id, 'status' => 'abandoned', 'rating' => 4, 'hours' => 5]);

        $s = (new Estadisticas)->de($user);

        $this->assertSame(3.0, $s['media']);
        $this->assertSame(15, $s["horas"]);
        $this->assertSame(2, $s['generos']['RPG']);
        $this->assertSame(50, $s['tasa_final']);
    }

    public function test_la_biblioteca_exige_login(): void
    {
        $this->get('/biblioteca')->assertRedirect('/entrar');
    }
}
