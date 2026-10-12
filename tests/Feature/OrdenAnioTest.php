<?php

namespace Tests\Feature;

use App\Models\Entry;
use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrdenAnioTest extends TestCase
{
    use RefreshDatabase;

    public function test_ordena_por_anio_con_los_sin_fecha_al_final(): void
    {
        $u = User::factory()->create();
        foreach ([['Viejo', '2001-05-01'], ['SinFecha', null], ['Nuevo', '2024-03-01']] as $i => [$n, $f]) {
            $g = Game::create(['rawg_id' => $i + 1, 'slug' => "j$i", 'name' => $n, 'released' => $f]);
            Entry::create(['user_id' => $u->id, 'game_id' => $g->id, 'status' => 'backlog']);
        }
        $this->actingAs($u)->get('/biblioteca?orden=anio')->assertSeeInOrder(['Nuevo', 'Viejo', 'SinFecha']);
        $this->actingAs($u)->get('/biblioteca?orden=anio_asc')->assertSeeInOrder(['Viejo', 'Nuevo', 'SinFecha']);
    }
}
