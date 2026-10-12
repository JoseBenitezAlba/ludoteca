<?php

namespace Tests\Feature;

use App\Models\Entry;
use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SorprendeTest extends TestCase
{
    use RefreshDatabase;

    public function test_recomienda_lo_que_no_tienes_y_del_genero_que_juegas(): void
    {
        Http::fake();
        $u = User::factory()->create();
        $mk = fn ($i, $g) => Game::create(['rawg_id' => $i, 'slug' => "g$i", 'name' => "Juego $i", 'image' => 'x', 'released' => '2020-01-01',
            'genres' => [$g], 'rawg_rating' => 4.2, 'ratings_count' => 900]);
        $mio = $mk(1, 'RPG');
        $rpg = $mk(2, 'RPG');
        $mk(3, 'Racing');
        Entry::create(['user_id' => $u->id, 'game_id' => $mio->id, 'status' => 'completed', 'rating' => 9]);

        $r = $this->actingAs($u)->getJson('/api/sorprende')->assertOk()->json('items');
        $slugs = array_column($r, 'slug');
        $this->assertNotContains('g1', $slugs);
        $this->assertContains('g2', $slugs);
        $this->assertNotContains('g3', $slugs);
    }

    public function test_invitado_recibe_populares(): void
    {
        Http::fake();
        Game::create(['rawg_id' => 9, 'slug' => 'g9', 'name' => 'Pop', 'image' => 'x', 'released' => '2019-01-01', 'rawg_rating' => 4.5, 'ratings_count' => 5000]);
        $this->getJson('/api/sorprende')->assertOk()->assertJsonPath('items.0.slug', 'g9');
    }
}
