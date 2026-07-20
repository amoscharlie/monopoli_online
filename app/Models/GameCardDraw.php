<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameCardDraw extends Model
{
    protected $fillable = [
        'game_id',
        'game_card_id',
        'player_id',
        'deck',
        'status',
        'snapshot',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
            'resolved_at' => 'datetime',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function card(): BelongsTo
    {
        return $this->belongsTo(GameCard::class, 'game_card_id');
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}
