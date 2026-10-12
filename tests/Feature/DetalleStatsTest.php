<?php

namespace Tests\Feature;

use App\Models\Entry;
use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DetalleStatsTest extends TestCase
{
    use RefreshDatabase;

    public function test_detalle_lista_los_juegos_de_cada_numero(): void
    {
        $u = User::factory()->create();
        $a = Game::create(['rawg_id' => 1, 'slug' => 'a', 'name' => 'Alfa', 'genres' => ['RPG'], 'playtime' => 20]);
        $b = Game::create(['rawg_id' => 2, 'slug' => 'b', 'name' => 'Beta', 'genres' => ['Action']]);
        Entry::create(['user_id' => $u->id, 'game_id' => $a->id, 'status' => 'completed', 'rating' => 9, 'hours' => 33]);
        Entry::create(['user_id' => $u->id, 'game_id' => $b->id, 'status' => 'backlog']);

        $this->get("/u/{$u->username}/detalle?tipo=genero&valor=RPG")->assertOk()->assertSee('Alfa')->assertDontSee('Beta');
        $this->get("/u/{$u->username}/detalle?tipo=estado&valor=backlog")->assertOk()->assertSee('Beta')->assertDontSee('Alfa');
        $this->get("/u/{$u->username}/detalle?tipo=nota&valor=9")->assertOk()->assertSee('Alfa');
        $this->get("/u/{$u->username}/detalle?tipo=horas")->assertOk()->assertSee('33');
        $this->get("/u/{$u->username}")->assertOk()->assertSee('Horas por juego');
    }
}
