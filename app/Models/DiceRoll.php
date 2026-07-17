<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiceRoll extends Model
{
    protected $fillable = [
        'game_id',
        'player_id',
        'dice_one',
        'dice_two',
        'total',
        'is_double',
        'turn_number',
        'result',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'dice_one' => 'integer',
            'dice_two' => 'integer',
            'total' => 'integer',
            'is_double' => 'boolean',
            'turn_number' => 'integer',
            'meta' => 'array',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}
