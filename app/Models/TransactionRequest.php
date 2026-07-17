<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionRequest extends Model
{
    protected $fillable = [
        'game_id',
        'player_id',
        'type',
        'target_player_id',
        'game_property_id',
        'amount',
        'source',
        'reason',
        'status',
        'payload',
        'decided_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'payload' => 'array',
            'decided_at' => 'datetime',
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

    public function targetPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'target_player_id');
    }

    public function gameProperty(): BelongsTo
    {
        return $this->belongsTo(GameProperty::class);
    }
}
