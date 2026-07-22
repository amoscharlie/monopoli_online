<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    protected $fillable = [
        'code',
        'status',
        'starting_balance',
        'go_bonus',
        'tax_amount',
        'fine_amount',
        'duration_minutes',
        'bank_cash_start',
        'bank_cash',
        'started_at',
        'ended_at',
        'winner_id',
        'last_scanned_player_id',
        'first_player_id',
        'current_turn_player_id',
        'turn_number',
        'turn_started_at',
        'turn_order',
        'finish_summary',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'turn_started_at' => 'datetime',
            'turn_order' => 'array',
            'finish_summary' => 'array',
        ];
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class)->orderBy('sort_order');
    }

    public function gameProperties(): HasMany
    {
        return $this->hasMany(GameProperty::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class)->latest();
    }

    public function playerAccessTokens(): HasMany
    {
        return $this->hasMany(PlayerAccessToken::class);
    }

    public function transactionRequests(): HasMany
    {
        return $this->hasMany(TransactionRequest::class)->latest();
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'winner_id');
    }

    public function lastScannedPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'last_scanned_player_id');
    }

    public function firstPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'first_player_id');
    }

    public function currentTurnPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'current_turn_player_id');
    }

    public function diceRolls(): HasMany
    {
        return $this->hasMany(DiceRoll::class)->latest();
    }

    public function cardDraws(): HasMany
    {
        return $this->hasMany(GameCardDraw::class)->latest();
    }

    public function jailFreeCardTransfers(): HasMany
    {
        return $this->hasMany(JailFreeCardTransfer::class)->latest();
    }
}
