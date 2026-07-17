<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'game_id',
        'type',
        'amount',
        'from_player_id',
        'to_player_id',
        'game_property_id',
        'description',
        'balance_after_from',
        'balance_after_to',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'balance_after_from' => 'integer',
            'balance_after_to' => 'integer',
            'meta' => 'array',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function fromPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'from_player_id');
    }

    public function toPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'to_player_id');
    }

    public function gameProperty(): BelongsTo
    {
        return $this->belongsTo(GameProperty::class);
    }
}
