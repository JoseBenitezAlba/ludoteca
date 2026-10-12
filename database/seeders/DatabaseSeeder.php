<?php

namespace Database\Seeders;

use App\Models\Entry;
use App\Models\Game;
use App\Models\GameList;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->catalogo();
        $this->demo();
    }

    private function catalogo(): void
    {
        $juegos = json_decode(file_get_contents(database_path('seeders/data/games.json')), true);
        foreach ($juegos as $g) {
            Game::updateOrCreate(['rawg_id' => $g['rawg_id']], [
                'slug' => $g['slug'],
                'name' => $g['name'],
                'image' => $g['image'],
                'released' => $g['released'],
                'genres' => $g['genres'],
                'platforms' => $g['platforms'],
                'metacritic' => $g['metacritic'],
                'rawg_rating' => $g['rating'],
                'ratings_count' => $g['ratings_count'] ?? 0,
                'playtime' => $g['playtime'],
            ]);
        }
    }

    private function demo(): void
    {
        if (User::where('username', 'demo')->exists()) {
            return;
        }

        $users = [
            ['demo', 'Cuenta demo', 'Probando la app. Todo lo que ves son datos de ejemplo.'],
            ['lucia', 'Lucía', 'Roguelikes y estrategia.'],
            ['marcos', 'Marcos', 'RPG largos y sin spoilers.'],
            ['nerea', 'Nerea', 'Platino o nada.'],
        ];
        $objs = [];
        foreach ($users as [$u, $n, $b]) {
            $objs[$u] = User::create(['name' => $n, 'username' => $u, 'email' => "$u@demo.test", 'password' => str()->random(32), 'bio' => $b]);
        }

        // [nombre, estado, nota (1-10), horas, plataforma, favorito, año de fin]
        $biblioteca = [
            'demo' => [
                ['The Witcher 3: Wild Hunt', 'platinum', 10, 180, 'PC', 1, 2024],
                ['Hades', 'completed', 9, 45, 'PC', 1, 2023],
                ['Elden Ring', 'completed', 9, 120, 'PC', 0, 2024],
                ['Forza Horizon 5', 'completed', 9, 70, 'PC', 0, 2025],
                ['Against the Storm', 'completed', 9, 60, 'PC', 0, 2025],
                ['Inscryption', 'completed', 8, 14, 'PC', 0, 2023],
                ['Baldur\'s Gate III', 'playing', null, 40, 'PC', 0, null],
                ['Cyberpunk 2077', 'completed', 8, 65, 'PC', 0, 2023],
                ['Death Stranding', 'abandoned', 5, 22, 'PS5', 0, null],
                ['A Plague Tale: Innocence', 'completed', 8, 12, 'PC', 0, 2022],
                ['Stardew Valley', 'completed', 9, 90, 'PC', 1, 2022],
                ['Hollow Knight', 'completed', 9, 38, 'PC', 0, 2023],
                ['Celeste', 'platinum', 9, 15, 'PC', 0, 2022],
                ['Crusader Kings III', 'playing', null, 55, 'PC', 0, null],
                ['Slay the Spire', 'completed', 8, 50, 'PC', 0, 2023],
                ['Red Dead Redemption 2', 'backlog', null, null, 'PC', 0, null],
                ['Disco Elysium', 'backlog', null, null, 'PC', 0, null],
                ['Persona 5 Royal', 'backlog', null, null, 'PS5', 0, null],
                ['Octopath Traveler', 'completed', 8, 55, 'PC', 0, 2024],
                ['Tales of Berseria', 'completed', 8, 48, 'PC', 0, 2024],
                ['Hi-Fi Rush', 'completed', 7, 12, 'PC', 0, 2024],
                ['Sekiro: Shadows Die Twice', 'abandoned', 6, 18, 'PC', 0, null],
                ['Stray', 'completed', 7, 6, 'PC', 0, 2023],
                ['Lies Of P', 'backlog', null, null, 'PC', 0, null],
                ['It Takes Two', 'completed', 9, 14, 'PC', 0, 2023],
            ],
            'lucia' => [
                ['Hades', 'platinum', 10, 90, 'PC', 1, 2023], ['Slay the Spire', 'completed', 9, 120, 'PC', 1, 2022],
                ['Balatro', 'completed', 9, 60, 'PC', 0, 2024], ['Dead Cells', 'completed', 8, 70, 'PC', 0, 2022],
                ['Against the Storm', 'playing', null, 25, 'PC', 0, null], ['Factorio', 'completed', 10, 200, 'PC', 1, 2023],
                ['Cities: Skylines', 'completed', 7, 80, 'PC', 0, 2021], ['Elden Ring', 'abandoned', 5, 30, 'PC', 0, null],
                ['Stardew Valley', 'completed', 9, 110, 'PC', 0, 2021], ['Hollow Knight', 'completed', 9, 40, 'PC', 0, 2022],
            ],
            'marcos' => [
                ['The Witcher 3: Wild Hunt', 'completed', 10, 150, 'PC', 1, 2022], ['Baldur\'s Gate III', 'completed', 10, 130, 'PC', 1, 2024],
                ['Cyberpunk 2077', 'completed', 9, 80, 'PC', 0, 2023], ['Elden Ring', 'completed', 10, 110, 'PC', 0, 2023],
                ['Persona 5 Royal', 'completed', 9, 110, 'PS5', 0, 2023], ['Disco Elysium', 'completed', 9, 28, 'PC', 0, 2022],
                ['Red Dead Redemption 2', 'completed', 10, 90, 'PC', 1, 2022], ['Death Stranding', 'completed', 7, 55, 'PC', 0, 2023],
                ['Hades', 'abandoned', 6, 10, 'PC', 0, null], ['Forza Horizon 5', 'backlog', null, null, 'PC', 0, null],
            ],
            'nerea' => [
                ['The Witcher 3: Wild Hunt', 'platinum', 9, 210, 'PS5', 0, 2022], ['God of War (2018)', 'platinum', 10, 60, 'PS5', 1, 2022],
                ['Horizon Zero Dawn', 'platinum', 9, 70, 'PS5', 0, 2023], ['Ghost of Tsushima', 'platinum', 9, 65, 'PS5', 0, 2023],
                ['Hades', 'completed', 8, 40, 'PC', 0, 2023], ['Elden Ring', 'completed', 9, 100, 'PS5', 0, 2023],
                ['Death Stranding', 'completed', 6, 50, 'PS5', 0, 2022], ['Cyberpunk 2077', 'platinum', 8, 110, 'PC', 0, 2024],
                ['Stray', 'platinum', 8, 8, 'PS5', 0, 2023], ['Forza Horizon 5', 'completed', 8, 60, 'PC', 0, 2024],
            ],
        ];

        foreach ($biblioteca as $username => $filas) {
            foreach ($filas as [$nombre, $estado, $nota, $horas, $plat, $fav, $anio]) {
                $game = Game::where('name', $nombre)->first();
                if (! $game) {
                    continue;
                }
                Entry::create([
                    'user_id' => $objs[$username]->id,
                    'game_id' => $game->id,
                    'status' => $estado,
                    'rating' => $nota,
                    'hours' => $horas,
                    'platform' => $plat,
                    'favorite' => (bool) $fav,
                    'finished_at' => $anio ? "$anio-".str_pad((string) (($game->id % 11) + 1), 2, '0', STR_PAD_LEFT).'-15' : null,
                    'created_at' => now()->subDays(rand(1, 300)),
                    'updated_at' => now()->subDays(rand(0, 40)),
                ]);
            }
        }

        $listas = [
            ['Para una tarde de lluvia', 'Juegos cortos que se disfrutan de un tirón.', ['Stray', 'Hi-Fi Rush', 'Inscryption', 'Celeste', 'A Plague Tale: Innocence']],
            ['RPG para perderse 100 horas', 'Mundos enormes y buenas historias.', ['The Witcher 3: Wild Hunt', 'Baldur\'s Gate III', 'Cyberpunk 2077', 'Elden Ring']],
        ];
        foreach ($listas as [$nombre, $desc, $juegos]) {
            $l = GameList::create(['user_id' => $objs['demo']->id, 'name' => $nombre, 'description' => $desc]);
            $ids = Game::whereIn('name', $juegos)->pluck('id');
            $l->games()->attach($ids);
        }
    }
}
