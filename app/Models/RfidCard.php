<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RfidCard extends Model
{
    protected $fillable = [
        'game_id',
        'player_id',
        'uid',
        'assigned_at',
        'last_scanned_at',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'last_scanned_at' => 'datetime',
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
