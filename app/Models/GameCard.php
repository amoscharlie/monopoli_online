<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameCard extends Model
{
    protected $fillable = [
        'deck',
        'key',
        'title',
        'description',
        'effect_type',
        'sort_order',
        'payload',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function draws(): HasMany
    {
        return $this->hasMany(GameCardDraw::class);
    }
}
