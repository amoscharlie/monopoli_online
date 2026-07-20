<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    protected $fillable = [
        'game_id',
        'name',
        'rfid_uid',
        'avatar_color',
        'balance',
        'cash_balance',
        'is_bankrupt',
        'is_in_jail',
        'double_streak',
        'jail_turn_count',
        'pending_jail_release',
        'jail_free_cards',
        'bankrupted_at',
        'bankrupt_summary',
        'sort_order',
        'board_position',
        'lap_count',
        'rules_unlocked',
        'pending_space_action',
    ];

    protected function casts(): array
    {
        return [
            'balance' => 'integer',
            'cash_balance' => 'integer',
            'is_bankrupt' => 'boolean',
            'is_in_jail' => 'boolean',
            'double_streak' => 'integer',
            'jail_turn_count' => 'integer',
            'pending_jail_release' => 'boolean',
            'jail_free_cards' => 'integer',
            'bankrupted_at' => 'datetime',
            'bankrupt_summary' => 'array',
            'board_position' => 'integer',
            'lap_count' => 'integer',
            'rules_unlocked' => 'boolean',
            'pending_space_action' => 'array',
        ];
    }

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function rfidCards(): HasMany
    {
        return $this->hasMany(RfidCard::class);
    }

    public function properties(): HasMany
    {
        return $this->hasMany(GameProperty::class, 'owner_id');
    }

    public function accessToken(): HasMany
    {
        return $this->hasMany(PlayerAccessToken::class);
    }

    public function transactionRequests(): HasMany
    {
        return $this->hasMany(TransactionRequest::class);
    }

    public function cardDraws(): HasMany
    {
        return $this->hasMany(GameCardDraw::class);
    }
}
