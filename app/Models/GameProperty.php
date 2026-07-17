<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameProperty extends Model
{
    protected $fillable = [
        'game_id',
        'property_id',
        'owner_id',
        'house_count',
        'has_hotel',
        'is_mortgaged',
    ];

    protected function casts(): array
    {
        return [
            'house_count' => 'integer',
            'has_hotel' => 'boolean',
            'is_mortgaged' => 'boolean',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'owner_id');
    }
}
