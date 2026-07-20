<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JailFreeCardTransfer extends Model
{
    protected $fillable = [
        'game_id',
        'from_player_id',
        'to_player_id',
        'game_card_draw_id',
        'amount',
        'status',
        'decided_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'decided_at' => 'datetime',
        ];
    }

    public function fromPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'from_player_id');
    }

    public function toPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'to_player_id');
    }

    public function draw(): BelongsTo
    {
        return $this->belongsTo(GameCardDraw::class, 'game_card_draw_id');
    }
}
