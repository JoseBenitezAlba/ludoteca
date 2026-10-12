<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'released' => 'date',
            'genres' => 'array',
            'platforms' => 'array',
            'developers' => 'array',
            'rawg_rating' => 'float',
            'detailed_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function entries(): HasMany
    {
        return $this->hasMany(Entry::class);
    }

    /** Imagen reducida de RAWG (carga mucho mas rapido que el original). */
    public function thumb(int $w = 600, int $h = 400): ?string // RAWG solo admite algunos tamaños; usamos siempre 600x400
    {
        if (! $this->image) {
            return null;
        }

        return str_contains($this->image, '/media/games/') || str_contains($this->image, '/media/screenshots/')
            ? str_replace('/media/', "/media/crop/600/400/", $this->image)
            : $this->image;
    }

    /** Todavía no ha salido: fecha de lanzamiento futura. */
    public function sinLanzar(): bool
    {
        return $this->released !== null && $this->released->isFuture();
    }

    public function year(): ?int
    {
        return $this->released?->year;
    }
}
