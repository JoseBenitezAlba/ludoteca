<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Entry extends Model
{
    public const STATUSES = [
        'playing' => 'Jugando',
        'backlog' => 'Pendiente',
        'completed' => 'Completado',
        'platinum' => 'Platinado',
        'abandoned' => 'Abandonado',
    ];

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'hours' => 'float',
            'finished_at' => 'date',
            'favorite' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /** Nota en estrellas (0,5 a 5). */
    public function stars(): ?float
    {
        return $this->rating ? $this->rating / 2 : null;
    }
}
