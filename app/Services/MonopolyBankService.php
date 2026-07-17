<?php

namespace App\Services;

use App\Models\Game;
use App\Models\GameProperty;
use App\Models\DiceRoll;
use App\Models\Player;
use App\Models\PlayerAccessToken;
use App\Models\Property;
use App\Models\RfidCard;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\TransactionRequest;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MonopolyBankService
{
    private const BOARD_SPACES = [
        ['index' => 0, 'name' => 'Start', 'type' => 'start'],
        ['index' => 1, 'name' => 'Indonesia', 'type' => 'property'],
        ['index' => 2, 'name' => 'Dana Umum', 'type' => 'community_card'],
        ['index' => 3, 'name' => 'Malaysia', 'type' => 'property'],
        ['index' => 4, 'name' => 'Pajak Jalan', 'type' => 'tax'],
        ['index' => 5, 'name' => 'Changi Airport', 'type' => 'transport'],
        ['index' => 6, 'name' => 'Singapore', 'type' => 'property'],
        ['index' => 7, 'name' => 'Kesempatan', 'type' => 'chance_card'],
        ['index' => 8, 'name' => 'Hongkong', 'type' => 'property'],
        ['index' => 9, 'name' => 'Taiwan', 'type' => 'property'],
        ['index' => 10, 'name' => 'Penjara Hanya Lewat', 'type' => 'jail_visit'],
        ['index' => 11, 'name' => 'Filipina', 'type' => 'property'],
        ['index' => 12, 'name' => 'Perusahaan Listrik', 'type' => 'utility'],
        ['index' => 13, 'name' => 'Thailand', 'type' => 'property'],
        ['index' => 14, 'name' => 'Vietnam', 'type' => 'property'],
        ['index' => 15, 'name' => 'Terminal Tokyo', 'type' => 'transport'],
        ['index' => 16, 'name' => 'Jepang', 'type' => 'property'],
        ['index' => 17, 'name' => 'Dana Umum', 'type' => 'community_card'],
        ['index' => 18, 'name' => 'Korea', 'type' => 'property'],
        ['index' => 19, 'name' => 'India', 'type' => 'property'],
        ['index' => 20, 'name' => 'Parkir Bebas', 'type' => 'free_parking'],
        ['index' => 21, 'name' => 'RRC (China)', 'type' => 'property'],
        ['index' => 22, 'name' => 'Kesempatan', 'type' => 'chance_card'],
        ['index' => 23, 'name' => 'Uni Soviet', 'type' => 'property'],
        ['index' => 24, 'name' => 'Italia', 'type' => 'property'],
        ['index' => 25, 'name' => 'Stasiun London', 'type' => 'transport'],
        ['index' => 26, 'name' => 'Inggris', 'type' => 'property'],
        ['index' => 27, 'name' => 'Perancis', 'type' => 'property'],
        ['index' => 28, 'name' => 'Perusahaan Air', 'type' => 'utility'],
        ['index' => 29, 'name' => 'Belanda', 'type' => 'property'],
        ['index' => 30, 'name' => 'Masuk Penjara', 'type' => 'go_to_jail'],
        ['index' => 31, 'name' => 'Kanada', 'type' => 'property'],
        ['index' => 32, 'name' => 'Amerika', 'type' => 'property'],
        ['index' => 33, 'name' => 'Dana Umum', 'type' => 'community_card'],
        ['index' => 34, 'name' => 'Brazilia', 'type' => 'property'],
        ['index' => 35, 'name' => 'Pelabuhan Sidney', 'type' => 'transport'],
        ['index' => 36, 'name' => 'Kesempatan', 'type' => 'chance_card'],
        ['index' => 37, 'name' => 'Australia', 'type' => 'property'],
        ['index' => 38, 'name' => 'Pajak Istimewa', 'type' => 'special_tax'],
        ['index' => 39, 'name' => 'Mesir', 'type' => 'property'],
    ];

    private array $avatarColors = [
        '#10b981',
        '#2563eb',
        '#f97316',
        '#8b5cf6',
        '#ec4899',
        '#eab308',
        '#14b8a6',
        '#ef4444',
    ];

    public function settingsPayload(): array
    {
        return [
            'starting_balance' => (int) Setting::getValue('starting_balance', 15000),
            'go_bonus' => (int) Setting::getValue('go_bonus', 20000),
            'tax_amount' => (int) Setting::getValue('tax_amount', 20000),
            'fine_amount' => (int) Setting::getValue('fine_amount', 10000),
            'starting_cash' => (int) Setting::getValue('starting_cash', 0),
        ];
    }

    public function createGame(array $payload): Game
    {
        if (Property::query()->doesntExist()) {
            throw ValidationException::withMessages([
                'properties' => 'Daftar properti masih kosong. Jalankan seeder atau tambahkan properti di Settings.',
            ]);
        }

        return DB::transaction(function () use ($payload) {
            $settings = $this->settingsPayload();
            $startingBalance = (int) ($payload['starting_balance'] ?? $settings['starting_balance']);
            $startingCash = (int) ($payload['starting_cash'] ?? $settings['starting_cash']);

            $game = Game::query()->create([
                'code' => $this->generateGameCode(),
                'status' => 'active',
                'starting_balance' => $startingBalance,
                'go_bonus' => (int) ($payload['go_bonus'] ?? $settings['go_bonus']),
                'tax_amount' => (int) ($payload['tax_amount'] ?? $settings['tax_amount']),
                'fine_amount' => (int) ($payload['fine_amount'] ?? $settings['fine_amount']),
                'duration_minutes' => $payload['duration_minutes'] ?? null,
                'started_at' => now(),
            ]);

            foreach ($payload['players'] as $index => $playerData) {
                $uid = $this->normalizeUid($playerData['rfid_uid'] ?? null);
                $player = $game->players()->create([
                    'name' => trim($playerData['name']),
                    'rfid_uid' => $uid,
                    'avatar_color' => $this->avatarColors[$index] ?? '#10b981',
                    'balance' => $startingBalance,
                    'cash_balance' => $startingCash,
                    'sort_order' => $index + 1,
                ]);

                if ($uid !== null) {
                    RfidCard::query()->create([
                        'game_id' => $game->id,
                        'player_id' => $player->id,
                        'uid' => $uid,
                        'assigned_at' => now(),
                    ]);
                }

                PlayerAccessToken::query()->create([
                    'game_id' => $game->id,
                    'player_id' => $player->id,
                    'token' => Str::random(48),
                ]);
            }

            Property::query()
                ->orderBy('sort_order')
                ->get()
                ->each(fn (Property $property) => GameProperty::query()->create([
                    'game_id' => $game->id,
                    'property_id' => $property->id,
                ]));

            $this->record($game, [
                'type' => 'game_started',
                'description' => "Game {$game->code} dimulai",
                'meta' => ['players' => count($payload['players'])],
            ]);

            return $game->fresh();
        });
    }

    public function openGames(): array
    {
        return Game::query()
            ->whereIn('status', ['active', 'paused'])
            ->with(['players', 'transactions'])
            ->latest('started_at')
            ->get()
            ->map(fn (Game $game) => $this->gameListItem($game))
            ->values()
            ->all();
    }

    public function historyGames(): array
    {
        return Game::query()
            ->where('status', 'finished')
            ->with(['players', 'winner', 'transactions'])
            ->latest('ended_at')
            ->get()
            ->map(fn (Game $game) => $this->gameListItem($game))
            ->values()
            ->all();
    }

    public function state(Game $game): array
    {
        $game->load([
            'players',
            'winner',
            'lastScannedPlayer',
            'firstPlayer',
            'currentTurnPlayer',
            'diceRolls.player',
            'gameProperties.property',
            'gameProperties.owner',
            'playerAccessTokens.player',
            'transactionRequests.player',
            'transactionRequests.targetPlayer',
            'transactionRequests.gameProperty.property',
            'transactions.fromPlayer',
            'transactions.toPlayer',
            'transactions.gameProperty.property',
        ]);

        $wealthRows = $this->wealthRows($game);
        $playerRows = $game->players
            ->map(fn (Player $player) => $this->playerPayload($player, $game->gameProperties, $wealthRows[$player->id] ?? null))
            ->sortBy([
                ['is_bankrupt', 'asc'],
                ['sort_order', 'asc'],
            ])
            ->values();
        $completeGroups = $this->completeGroups($game);
        $propertyRows = $game->gameProperties
            ->sortBy('property.sort_order')
            ->map(fn (GameProperty $gameProperty) => $this->propertyPayload($gameProperty, $completeGroups))
            ->values();
        $transactions = $game->transactions
            ->take(50)
            ->map(fn (Transaction $transaction) => $this->transactionPayload($transaction))
            ->values();

        $ownedProperties = $game->gameProperties->whereNotNull('owner_id');
        $activePlayerRows = $playerRows->reject(fn ($player) => $player['is_bankrupt']);
        $richest = $activePlayerRows->sortByDesc('total_asset')->first();
        $poorest = $activePlayerRows->sortBy('total_asset')->first();
        $ownershipSummary = $this->ownershipSummary($playerRows);

        return [
            'game' => [
                'id' => $game->id,
                'code' => $game->code,
                'status' => $game->status,
                'status_label' => Str::headline($game->status),
                'started_at' => $game->started_at?->toIso8601String(),
                'started_at_label' => $game->started_at?->format('d M Y H:i'),
                'ended_at' => $game->ended_at?->toIso8601String(),
                'ended_at_label' => $game->ended_at?->format('d M Y H:i'),
                'duration_seconds' => $this->durationSeconds($game),
                'duration_minutes' => $game->duration_minutes,
                'ends_at' => $this->endsAt($game)?->toIso8601String(),
                'ends_at_label' => $this->endsAt($game)?->format('d M Y H:i'),
                'remaining_seconds' => $this->remainingSeconds($game),
                'is_time_up' => $this->remainingSeconds($game) === 0 && $game->duration_minutes !== null,
                'starting_balance' => $game->starting_balance,
                'go_bonus' => $game->go_bonus,
                'tax_amount' => $game->tax_amount,
                'fine_amount' => $game->fine_amount,
                'player_count' => $game->players->count(),
                'total_money' => $game->players->sum('balance'),
                'total_cash' => $game->players->sum('cash_balance'),
                'total_player_money' => $game->players->sum(fn (Player $player) => $player->balance + $player->cash_balance),
                'winner_id' => $game->winner_id,
                'winner_name' => $game->winner?->name,
                'last_scanned_player_id' => $game->last_scanned_player_id,
                'last_scanned_player' => $game->lastScannedPlayer ? $this->playerBrief($game->lastScannedPlayer) : null,
                'first_player_id' => $game->first_player_id,
                'first_player' => $game->firstPlayer ? $this->playerBrief($game->firstPlayer) : null,
                'needs_first_player_spin' => $game->first_player_id === null,
                'current_turn_player_id' => $game->current_turn_player_id,
                'current_turn_player' => $game->currentTurnPlayer ? $this->playerBrief($game->currentTurnPlayer) : null,
                'turn_number' => $game->turn_number,
                'turn_order' => $game->turn_order ?? [],
            ],
            'players' => $playerRows,
            'properties' => $propertyRows,
            'transactions' => $transactions,
            'cards' => [
                'used_keys' => $this->usedCardKeys($game),
            ],
            'player_portal' => [
                'players' => $this->playerPortalLinks($game),
                'pending_requests' => $game->transactionRequests
                    ->where('status', 'pending')
                    ->map(fn (TransactionRequest $request) => $this->transactionRequestPayload($request))
                    ->values()
                    ->all(),
            ],
            'stats' => [
                'transaction_count' => $game->transactions->count(),
                'owned_property_count' => $ownedProperties->count(),
                'sold_house_count' => $game->gameProperties->sum('house_count'),
                'sold_hotel_count' => $game->gameProperties->where('has_hotel', true)->count(),
                'total_house_count' => 32,
                'total_hotel_count' => 12,
                'richest_player' => $richest,
                'poorest_player' => $poorest,
                'top_property_owner' => $activePlayerRows->sortByDesc('property_count')->first(),
                'total_assets' => $activePlayerRows->sum('total_asset'),
                'total_property_value' => $playerRows->sum('property_value'),
                'total_building_value' => $playerRows->sum(fn ($player) => $player['house_value'] + $player['hotel_value']),
                'ownership_summary' => $ownershipSummary,
            ],
            'turn' => $this->turnPayload($game),
            'board' => [
                'spaces' => self::BOARD_SPACES,
                'jail_position' => 10,
                'start_position' => 0,
            ],
            'charts' => [
                'wealth' => [
                    'labels' => ['Awal', 'Sekarang'],
                    'datasets' => $this->wealthHistoryChart($game, $playerRows),
                ],
                'transactions' => $this->transactionChart($game->transactions),
                'ownership' => [
                    'labels' => $playerRows->pluck('name')->push('Bank')->all(),
                    'data' => $playerRows->pluck('property_count')->push($game->gameProperties->whereNull('owner_id')->count())->all(),
                    'colors' => $playerRows->pluck('avatar_color')->push('#64748b')->all(),
                ],
            ],
        ];
    }

    public function pause(Game $game): Game
    {
        $this->ensureNotFinished($game);
        $game->update(['status' => 'paused']);

        $this->record($game, [
            'type' => 'game_paused',
            'description' => "Game {$game->code} dijeda",
        ]);

        return $game->fresh();
    }

    public function resume(Game $game): Game
    {
        $this->ensureNotFinished($game);
        $game->update(['status' => 'active']);

        $this->record($game, [
            'type' => 'game_resumed',
            'description' => "Game {$game->code} dilanjutkan",
        ]);

        return $game->fresh();
    }

    public function refreshCards(Game $game): Transaction
    {
        $this->ensureNotFinished($game);

        return $this->record($game, [
            'type' => 'card_refresh',
            'description' => 'Kartu Dana Umum & Kesempatan di-refresh',
        ]);
    }

    public function bankruptcyPreview(Game $game, int $playerId): array
    {
        $game->load(['gameProperties.property']);
        $player = Player::query()
            ->where('game_id', $game->id)
            ->whereKey($playerId)
            ->firstOrFail();

        return $this->bankruptcySummary($player, $game->gameProperties);
    }

    public function rentPreview(Game $game, int $payerPlayerId, int $gamePropertyId, string $source = 'bank'): array
    {
        $game->load(['gameProperties.property']);
        $property = GameProperty::query()
            ->with(['property', 'owner'])
            ->where('game_id', $game->id)
            ->whereKey($gamePropertyId)
            ->first();

        if (! $property) {
            throw ValidationException::withMessages(['property' => 'Properti tidak ditemukan pada game ini.']);
        }

        $payer = Player::query()
            ->where('game_id', $game->id)
            ->whereKey($payerPlayerId)
            ->first();

        if (! $payer) {
            throw ValidationException::withMessages(['player' => 'Pemain tidak ditemukan pada game ini.']);
        }

        $this->validateRentTarget($property, $payer);

        $source = in_array($source, ['bank', 'cash'], true) ? $source : 'bank';
        $rent = $this->rentDetails($game, $property, $payer);
        $bankruptcy = $this->bankruptcySummary($payer, $game->gameProperties);
        $available = $source === 'cash' ? $payer->cash_balance : $payer->balance;
        $totalLiquidation = $payer->balance + $payer->cash_balance + $bankruptcy['sale_total'];

        return [
            ...$rent,
            'source' => $source,
            'available' => $available,
            'shortage' => max(0, $rent['amount'] - $available),
            'can_pay_now' => $available >= $rent['amount'],
            'can_cover_by_selling_assets' => $totalLiquidation >= $rent['amount'],
            'must_bankrupt' => $totalLiquidation < $rent['amount'],
            'bankruptcy' => $bankruptcy + [
                'liquid_balance' => $payer->balance + $payer->cash_balance,
                'total_liquidation' => $totalLiquidation,
                'payable_to_owner' => min($rent['amount'], $totalLiquidation),
            ],
        ];
    }

    public function bankruptPlayer(Game $game, int $playerId): Player
    {
        return DB::transaction(function () use ($game, $playerId) {
            $this->ensureActive($game);
            $player = Player::query()
                ->where('game_id', $game->id)
                ->whereKey($playerId)
                ->lockForUpdate()
                ->first();

            if (! $player) {
                throw ValidationException::withMessages(['player' => 'Pemain tidak ditemukan pada game ini.']);
            }

            if ($player->is_bankrupt) {
                throw ValidationException::withMessages(['player' => "{$player->name} sudah bangkrut."]);
            }

            $gameProperties = GameProperty::query()
                ->with('property')
                ->where('game_id', $game->id)
                ->where('owner_id', $player->id)
                ->lockForUpdate()
                ->get();
            $summary = $this->bankruptcySummary($player, $gameProperties);

            $gameProperties->each(fn (GameProperty $gameProperty) => $gameProperty->update([
                'owner_id' => null,
                'house_count' => 0,
                'has_hotel' => false,
                'is_mortgaged' => false,
            ]));

            $player->update([
                'balance' => $player->balance + $summary['sale_total'],
                'is_bankrupt' => true,
                'bankrupted_at' => now(),
                'bankrupt_summary' => $summary,
            ]);

            if ((int) $game->last_scanned_player_id === (int) $player->id) {
                $game->update(['last_scanned_player_id' => null]);
            }

            if ((int) $game->current_turn_player_id === (int) $player->id) {
                $this->advanceTurn($game, $player);
            }

            $this->record($game, [
                'type' => 'player_bankrupt',
                'to_player_id' => $player->id,
                'amount' => $summary['sale_total'],
                'description' => "{$player->name} bangkrut dan menjual semua aset ke Bank",
                'balance_after_to' => $player->fresh()->balance,
                'meta' => $summary,
            ]);

            return $player->fresh();
        });
    }

    public function setFirstPlayer(Game $game, int $playerId): Game
    {
        return DB::transaction(function () use ($game, $playerId) {
            $this->ensureNotFinished($game);

            if ($game->first_player_id) {
                return $game->fresh();
            }

            $player = $this->playerForUpdate($game, $playerId);
            $order = $this->buildTurnOrder($game, $player->id);
            $game->update([
                'first_player_id' => $player->id,
                'current_turn_player_id' => $player->id,
                'turn_order' => $order,
                'turn_number' => 1,
            ]);

            $this->record($game, [
                'type' => 'first_player_selected',
                'to_player_id' => $player->id,
                'description' => "{$player->name} terpilih sebagai pemain pertama",
            ]);

            return $game->fresh();
        });
    }

    public function reset(Game $game): Game
    {
        return DB::transaction(function () use ($game) {
            $this->ensureNotFinished($game);

            $startingCash = (int) Setting::getValue('starting_cash', 0);
            $game->players()->update([
                'balance' => $game->starting_balance,
                'cash_balance' => $startingCash,
                'is_bankrupt' => false,
                'is_in_jail' => false,
                'double_streak' => 0,
                'jail_turn_count' => 0,
                'pending_jail_release' => false,
                'jail_free_cards' => 0,
                'board_position' => 0,
                'lap_count' => 0,
                'rules_unlocked' => false,
                'pending_space_action' => null,
                'bankrupted_at' => null,
                'bankrupt_summary' => null,
            ]);
            $game->gameProperties()->update([
                'owner_id' => null,
                'house_count' => 0,
                'has_hotel' => false,
                'is_mortgaged' => false,
            ]);
            $game->transactions()->delete();
            $game->update([
                'status' => 'active',
                'winner_id' => null,
                'last_scanned_player_id' => null,
                'finish_summary' => null,
                'ended_at' => null,
                'current_turn_player_id' => null,
                'turn_order' => null,
                'turn_number' => 1,
            ]);
            $game->diceRolls()->delete();

            $this->record($game, [
                'type' => 'game_reset',
                'description' => "Game {$game->code} di-reset",
            ]);

            return $game->fresh();
        });
    }

    public function finish(Game $game): Game
    {
        return DB::transaction(function () use ($game) {
            $this->ensureNotFinished($game);

            $state = $this->state($game);
            $winner = collect($state['players'])
                ->reject(fn ($player) => data_get($player, 'is_bankrupt'))
                ->sortByDesc('total_asset')
                ->first();

            $game->update([
                'status' => 'finished',
                'ended_at' => now(),
                'winner_id' => $winner['id'] ?? null,
                'finish_summary' => [
                    'players' => $state['players'],
                    'stats' => $state['stats'],
                    'transactions' => $state['transactions'],
                ],
            ]);

            $this->record($game, [
                'type' => 'game_finished',
                'to_player_id' => $winner['id'] ?? null,
                'description' => ($winner['name'] ?? 'Pemenang') . " memenangkan {$game->code}",
                'meta' => ['winner' => $winner],
            ]);

            return $game->fresh();
        });
    }

    public function scanRfid(string $uid, ?int $gameId = null): array
    {
        $uid = $this->normalizeUid($uid);
        $game = $gameId
            ? Game::query()->findOrFail($gameId)
            : Game::query()->whereIn('status', ['active', 'paused'])->latest('started_at')->first();

        if (! $game) {
            throw ValidationException::withMessages(['uid' => 'Tidak ada game aktif untuk menerima RFID.']);
        }

        $card = RfidCard::query()
            ->where('game_id', $game->id)
            ->where('uid', $uid)
            ->with('player')
            ->first();

        if (! $card) {
            return [
                'matched' => false,
                'uid' => $uid,
                'message' => 'UID RFID tidak terdaftar pada game ini.',
                'state' => $this->state($game),
            ];
        }

        if ($card->player->is_bankrupt) {
            return [
                'matched' => true,
                'uid' => $uid,
                'bankrupt' => true,
                'message' => "{$card->player->name} sudah bangkrut.",
                'player' => $this->playerBrief($card->player),
                'state' => $this->state($game),
            ];
        }

        $card->update(['last_scanned_at' => now()]);
        $game->update(['last_scanned_player_id' => $card->player_id]);

        $this->record($game, [
            'type' => 'rfid_scan',
            'to_player_id' => $card->player_id,
            'description' => "{$card->player->name} scan RFID",
            'meta' => ['uid' => $uid],
        ]);

        return [
            'matched' => true,
            'uid' => $uid,
            'player' => $this->playerBrief($card->player),
            'state' => $this->state($game->fresh()),
        ];
    }

    public function submitPlayerRequest(PlayerAccessToken $accessToken, array $payload): TransactionRequest
    {
        $accessToken->update(['last_seen_at' => now()]);
        $game = $accessToken->game;
        $player = $accessToken->player;

        if ($game->status !== 'active') {
            throw ValidationException::withMessages(['game' => 'Permainan belum aktif.']);
        }

        if ($player->is_bankrupt) {
            throw ValidationException::withMessages(['player' => "{$player->name} sudah bangkrut."]);
        }

        $type = $payload['type'];
        $property = isset($payload['game_property_id'])
            ? GameProperty::query()->with('property')->where('game_id', $game->id)->find($payload['game_property_id'])
            : null;
        $target = isset($payload['target_player_id'])
            ? Player::query()->where('game_id', $game->id)->find($payload['target_player_id'])
            : null;

        $description = $this->requestDescription($player, $type, $property, $target, $payload);
        $this->validatePlayerRequest($player, $type, $property, $target, $payload);
        $this->ensureNoDuplicatePendingRequest($game, $player, $type, $property, $target, $payload);

        return TransactionRequest::query()->create([
            'game_id' => $game->id,
            'player_id' => $player->id,
            'type' => $type,
            'target_player_id' => $target?->id,
            'game_property_id' => $property?->id,
            'amount' => $payload['amount'] ?? null,
            'source' => $payload['source'] ?? null,
            'reason' => $payload['reason'] ?? $description,
            'payload' => $payload,
        ]);
    }

    public function approveRequest(Game $game, int $requestId): TransactionRequest
    {
        return DB::transaction(function () use ($game, $requestId) {
            $request = TransactionRequest::query()
                ->where('game_id', $game->id)
                ->whereKey($requestId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($request->status !== 'pending') {
                throw ValidationException::withMessages(['request' => 'Permintaan ini sudah diproses.']);
            }

            $payload = $request->payload ?? [];
            match ($request->type) {
                'transfer' => $this->transfer($game, $request->player_id, (int) $request->target_player_id, (int) $request->amount, $request->source ?: 'bank'),
                'player_to_bank' => $this->playerToBank($game, $request->player_id, (int) $request->amount, $request->reason),
                'bank_to_player' => $this->bankToPlayer($game, $request->player_id, (int) $request->amount, $request->reason),
                'deposit' => $this->deposit($game, $request->player_id, (int) $request->amount, $request->reason),
                'withdraw' => $this->withdraw($game, $request->player_id, (int) $request->amount, $request->reason),
                'buy_property' => $this->buyProperty($game, $request->player_id, (int) $request->game_property_id),
                'sell_property' => $this->sellProperty($game, $request->player_id, (int) $request->game_property_id),
                'add_house' => $this->addHouse($game, $request->player_id, (int) $request->game_property_id),
                'sell_house' => $this->sellHouse($game, $request->player_id, (int) $request->game_property_id),
                'add_hotel' => $this->addHotel($game, $request->player_id, (int) $request->game_property_id),
                'sell_hotel' => $this->sellHotel($game, $request->player_id, (int) $request->game_property_id),
                'pay_rent' => $this->payRent($game, $request->player_id, (int) $request->game_property_id, $request->source ?: 'bank'),
                'pay_jail_fee' => $this->payJailFee($game, $request->player_id),
                'use_jail_card' => $this->useJailCard($game, $request->player_id),
                default => throw ValidationException::withMessages(['type' => 'Jenis permintaan belum didukung.']),
            };

            $request->update([
                'status' => 'approved',
                'decided_at' => now(),
            ]);

            return $request->fresh();
        });
    }

    public function rejectRequest(Game $game, int $requestId): TransactionRequest
    {
        $request = TransactionRequest::query()
            ->where('game_id', $game->id)
            ->whereKey($requestId)
            ->firstOrFail();

        if ($request->status !== 'pending') {
            throw ValidationException::withMessages(['request' => 'Permintaan ini sudah diproses.']);
        }

        $request->update([
            'status' => 'rejected',
            'decided_at' => now(),
        ]);

        return $request->fresh();
    }

    public function transfer(Game $game, int $fromPlayerId, int $toPlayerId, int $amount, string $source = 'bank'): Transaction
    {
        return DB::transaction(function () use ($game, $fromPlayerId, $toPlayerId, $amount, $source) {
            $this->ensureActive($game);
            $this->ensureDifferentPlayers($fromPlayerId, $toPlayerId);
            $amount = $this->positiveAmount($amount);
            $source = in_array($source, ['bank', 'cash'], true) ? $source : 'bank';

            $from = $this->playerForUpdate($game, $fromPlayerId);
            $to = $this->playerForUpdate($game, $toPlayerId);

            if ($source === 'cash') {
                $this->ensureCashFunds($from, $amount);
                $from->decrement('cash_balance', $amount);
                $to->increment('cash_balance', $amount);
            } else {
                $this->ensureFunds($from, $amount);
                $from->decrement('balance', $amount);
                $to->increment('balance', $amount);
            }

            $sourceLabel = $source === 'cash' ? 'cash fisik' : 'saldo bank';

            return $this->record($game, [
                'type' => 'transfer',
                'amount' => $amount,
                'from_player_id' => $from->id,
                'to_player_id' => $to->id,
                'description' => "{$from->name} transfer {$amount} ke {$to->name} via {$sourceLabel}",
                'balance_after_from' => $from->fresh()->balance,
                'balance_after_to' => $to->fresh()->balance,
                'meta' => [
                    'source' => $source,
                    'cash_after_from' => $from->fresh()->cash_balance,
                    'cash_after_to' => $to->fresh()->cash_balance,
                ],
            ]);
        });
    }

    public function payRent(Game $game, int $payerPlayerId, int $gamePropertyId, string $source = 'bank'): Transaction
    {
        return DB::transaction(function () use ($game, $payerPlayerId, $gamePropertyId, $source) {
            $this->ensureActive($game);
            $source = in_array($source, ['bank', 'cash'], true) ? $source : 'bank';
            $property = $this->gamePropertyForUpdate($game, $gamePropertyId);
            $payer = $this->playerForUpdate($game, $payerPlayerId);
            $this->validateRentTarget($property, $payer);
            $owner = $this->playerForUpdate($game, (int) $property->owner_id);
            $this->ensureRentNotRecentlyPaid($game, $payer, $property);
            $rent = $this->rentDetails($game, $property, $payer);
            $amount = $rent['amount'];

            $game->loadMissing(['gameProperties.property']);
            $bankruptcy = $this->bankruptcySummary($payer, $game->gameProperties);
            $totalLiquidation = $payer->balance + $payer->cash_balance + $bankruptcy['sale_total'];

            if (($source === 'cash' && $payer->cash_balance < $amount) || ($source === 'bank' && $payer->balance < $amount)) {
                if ($totalLiquidation < $amount) {
                    return $this->bankruptForRent($game, $payer, $owner, $property, $rent, $bankruptcy);
                }

                throw ValidationException::withMessages([
                    'balance' => "{$payer->name} belum cukup membayar sewa. Jual aset dulu atau pilih sumber uang lain.",
                ]);
            }

            if ($source === 'cash') {
                $payer->decrement('cash_balance', $amount);
                $owner->increment('cash_balance', $amount);
            } else {
                $payer->decrement('balance', $amount);
                $owner->increment('balance', $amount);
            }

            $sourceLabel = $source === 'cash' ? 'cash fisik' : 'saldo bank';
            $multiplier = $rent['rule_label'] ? " ({$rent['rule_label']})" : '';

            return $this->record($game, [
                'type' => 'pay_rent',
                'amount' => $amount,
                'from_player_id' => $payer->id,
                'to_player_id' => $owner->id,
                'game_property_id' => $property->id,
                'description' => "{$payer->name} bayar sewa {$property->property->name} {$amount} ke {$owner->name} via {$sourceLabel}{$multiplier}",
                'balance_after_from' => $payer->fresh()->balance,
                'balance_after_to' => $owner->fresh()->balance,
                'meta' => [
                    'source' => $source,
                    'complete_group' => $rent['owner_complete_group'],
                    'utility_full_groups' => $rent['payer_complete_group_count'],
                    'multiplier' => $rent['multiplier'],
                    'rent_rule' => $rent['rule'],
                    'house_count' => $property->house_count,
                    'has_hotel' => $property->has_hotel,
                    'cash_after_from' => $payer->fresh()->cash_balance,
                    'cash_after_to' => $owner->fresh()->cash_balance,
                ],
            ]);
        });
    }

    public function rollDice(Game $game, int $playerId): DiceRoll
    {
        return DB::transaction(function () use ($game, $playerId) {
            $this->ensureActive($game);

            $game = Game::query()
                ->with('players')
                ->whereKey($game->id)
                ->lockForUpdate()
                ->firstOrFail();

            $player = Player::query()
                ->where('game_id', $game->id)
                ->whereKey($playerId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($player->is_bankrupt) {
                throw ValidationException::withMessages(['player' => "{$player->name} sudah bangkrut."]);
            }

            $pendingPlayer = Player::query()
                ->where('game_id', $game->id)
                ->whereNotNull('pending_space_action')
                ->first();

            if ($pendingPlayer) {
                throw ValidationException::withMessages(['board' => "Selesaikan aksi petak {$pendingPlayer->name} dulu."]);
            }

            if (! $game->current_turn_player_id) {
                $order = $this->buildTurnOrder($game, $game->first_player_id ?: $player->id);
                $game->update([
                    'current_turn_player_id' => $order[0] ?? $player->id,
                    'turn_order' => $order,
                    'turn_number' => $game->turn_number ?: 1,
                ]);
                $game->refresh();
            }

            if ((int) $game->current_turn_player_id !== (int) $player->id) {
                $currentTurnName = $game->currentTurnPlayer?->name ?? 'pemain lain';
                throw ValidationException::withMessages(['turn' => "Sekarang giliran {$currentTurnName}."]);
            }

            $diceOne = random_int(1, 6);
            $diceTwo = random_int(1, 6);
            $isDouble = $diceOne === $diceTwo;
            $result = 'normal';
            $advanceTurn = true;
            $meta = [];
            $positionResult = null;

            if ($player->is_in_jail) {
                $attempt = $player->jail_turn_count + 1;
                $releaseNow = $isDouble || $attempt >= 4 || $player->pending_jail_release;
                $result = $releaseNow ? 'jail_released' : 'jail_wait';
                $meta['jail_attempt'] = $attempt;

                $player->update([
                    'is_in_jail' => ! $releaseNow,
                    'jail_turn_count' => $releaseNow ? 0 : $attempt,
                    'pending_jail_release' => false,
                    'double_streak' => 0,
                ]);
            } else {
                $doubleStreak = $isDouble ? $player->double_streak + 1 : 0;

                if ($doubleStreak >= 3 && $player->rules_unlocked) {
                    $result = 'go_to_jail';
                    $player->update([
                        'board_position' => 10,
                        'is_in_jail' => true,
                        'jail_turn_count' => 0,
                        'pending_jail_release' => false,
                        'pending_space_action' => null,
                        'double_streak' => 0,
                    ]);
                } else {
                    $result = $isDouble ? 'double' : 'normal';
                    $advanceTurn = ! $isDouble;
                    $positionResult = $this->movePlayerByDice($game, $player, $diceOne + $diceTwo);
                    $player->update(['double_streak' => $isDouble ? min($doubleStreak, 3) : 0]);
                    $meta['movement'] = $positionResult;

                    if ($isDouble && ! $player->fresh()->rules_unlocked && $doubleStreak >= 3) {
                        $advanceTurn = true;
                        $result = 'double_limit_before_unlock';
                    }
                }
            }

            $roll = DiceRoll::query()->create([
                'game_id' => $game->id,
                'player_id' => $player->id,
                'dice_one' => $diceOne,
                'dice_two' => $diceTwo,
                'total' => $diceOne + $diceTwo,
                'is_double' => $isDouble,
                'turn_number' => $game->turn_number ?: 1,
                'result' => $result,
                'meta' => $meta,
            ]);

            if ($advanceTurn || $result === 'go_to_jail' || $result === 'jail_wait' || $result === 'jail_released') {
                $this->advanceTurn($game, $player);
            }

            $this->record($game, [
                'type' => 'dice_roll',
                'to_player_id' => $player->id,
                'amount' => $diceOne + $diceTwo,
                'description' => "{$player->name} kocok dadu {$diceOne} + {$diceTwo} = " . ($diceOne + $diceTwo),
                'meta' => [
                    'dice_roll_id' => $roll->id,
                    'dice_one' => $diceOne,
                    'dice_two' => $diceTwo,
                    'is_double' => $isDouble,
                    'result' => $result,
                    'movement' => $positionResult,
                ],
            ]);

            return $roll->fresh('player');
        });
    }

    public function payJailFee(Game $game, int $playerId): Transaction
    {
        return DB::transaction(function () use ($game, $playerId) {
            $this->ensureActive($game);
            $player = $this->playerForUpdate($game, $playerId);

            if (! $player->is_in_jail) {
                throw ValidationException::withMessages(['jail' => "{$player->name} tidak sedang di penjara."]);
            }

            $this->ensureFunds($player, 5000);
            $player->decrement('balance', 5000);
            $player->update(['pending_jail_release' => true]);

            return $this->record($game, [
                'type' => 'pay_jail_fee',
                'amount' => 5000,
                'from_player_id' => $player->id,
                'description' => "{$player->name} membayar 5000 ke Bank untuk bebas penjara mulai giliran berikutnya",
                'balance_after_from' => $player->fresh()->balance,
            ]);
        });
    }

    public function resolveSpaceAction(Game $game, int $playerId, string $decision, string $source = 'bank'): ?Transaction
    {
        return DB::transaction(function () use ($game, $playerId, $decision, $source) {
            $this->ensureActive($game);
            $player = $this->playerForUpdate($game, $playerId);
            $action = $player->pending_space_action;

            if (! $action) {
                throw ValidationException::withMessages(['board' => 'Tidak ada aksi petak yang perlu diselesaikan.']);
            }

            $source = in_array($source, ['bank', 'cash'], true) ? $source : 'bank';
            $type = $action['action'] ?? 'info';
            $transaction = null;

            if ($decision === 'skip') {
                $player->update(['pending_space_action' => null]);
                $this->record($game, [
                    'type' => 'space_skipped',
                    'to_player_id' => $player->id,
                    'description' => "{$player->name} melewati aksi {$action['space_name']}",
                    'meta' => ['space_action' => $action],
                ]);
                return null;
            }

            if ($type === 'buy_property' && $decision === 'buy') {
                $transaction = $this->buyProperty($game, $player->id, (int) $action['game_property_id']);
            } elseif ($type === 'pay_rent' && $decision === 'pay') {
                $transaction = $this->payRent($game, $player->id, (int) $action['game_property_id'], $source);
            } elseif (in_array($type, ['pay_tax', 'pay_special_tax'], true) && $decision === 'pay') {
                $amount = (int) ($action['amount'] ?? 0);
                $transaction = $this->playerToBank($game, $player->id, $amount, $action['label'] ?? 'Bayar pajak');
            } else {
                throw ValidationException::withMessages(['board' => 'Pilihan aksi petak tidak sesuai.']);
            }

            Player::query()
                ->whereKey($player->id)
                ->update(['pending_space_action' => null]);

            return $transaction;
        });
    }

    public function useJailCard(Game $game, int $playerId): Transaction
    {
        return DB::transaction(function () use ($game, $playerId) {
            $this->ensureActive($game);
            $player = $this->playerForUpdate($game, $playerId);

            if (! $player->is_in_jail) {
                throw ValidationException::withMessages(['jail' => "{$player->name} tidak sedang di penjara."]);
            }

            if ($player->jail_free_cards <= 0) {
                throw ValidationException::withMessages(['jail_free_cards' => "{$player->name} belum punya kartu bebas penjara."]);
            }

            $player->decrement('jail_free_cards');
            $player->update(['pending_jail_release' => true]);

            return $this->record($game, [
                'type' => 'use_jail_card',
                'to_player_id' => $player->id,
                'description' => "{$player->name} memakai kartu bebas penjara untuk bebas mulai giliran berikutnya",
                'balance_after_to' => $player->fresh()->balance,
            ]);
        });
    }

    public function collectFromPlayers(Game $game, int $toPlayerId, int $amount, ?string $reason = null, ?string $cardKey = null): Transaction
    {
        return DB::transaction(function () use ($game, $toPlayerId, $amount, $reason, $cardKey) {
            $this->ensureActive($game);
            $amount = $this->positiveAmount($amount);
            $this->ensureCardAvailable($game, $cardKey);

            $to = $this->playerForUpdate($game, $toPlayerId);
            $payers = Player::query()
                ->where('game_id', $game->id)
                ->whereKeyNot($toPlayerId)
                ->where('is_bankrupt', false)
                ->lockForUpdate()
                ->get();

            foreach ($payers as $payer) {
                $this->ensureFunds($payer, $amount);
            }

            foreach ($payers as $payer) {
                $payer->decrement('balance', $amount);
            }

            $total = $amount * $payers->count();
            $to->increment('balance', $total);

            return $this->record($game, [
                'type' => 'collect_from_players',
                'amount' => $total,
                'to_player_id' => $to->id,
                'description' => "{$to->name} menerima {$amount} dari tiap pemain" . ($reason ? " ({$reason})" : ''),
                'balance_after_to' => $to->fresh()->balance,
                'meta' => [
                    'card_key' => $cardKey,
                    'amount_each' => $amount,
                    'payer_count' => $payers->count(),
                    'payer_ids' => $payers->pluck('id')->all(),
                ],
            ]);
        });
    }

    public function deposit(Game $game, int $playerId, int $amount, ?string $reason = null): Transaction
    {
        return DB::transaction(function () use ($game, $playerId, $amount, $reason) {
            $this->ensureActive($game);
            $amount = $this->positiveAmount($amount);
            $player = $this->playerForUpdate($game, $playerId);
            $this->ensureCashFunds($player, $amount);
            $player->decrement('cash_balance', $amount);
            $player->increment('balance', $amount);

            return $this->record($game, [
                'type' => 'deposit',
                'amount' => $amount,
                'to_player_id' => $player->id,
                'description' => "{$player->name} deposit cash {$amount}" . ($reason ? " ({$reason})" : ''),
                'balance_after_to' => $player->fresh()->balance,
            ]);
        });
    }

    public function withdraw(Game $game, int $playerId, int $amount, ?string $reason = null): Transaction
    {
        return DB::transaction(function () use ($game, $playerId, $amount, $reason) {
            $this->ensureActive($game);
            $amount = $this->positiveAmount($amount);
            $player = $this->playerForUpdate($game, $playerId);
            $this->ensureFunds($player, $amount);
            $player->decrement('balance', $amount);
            $player->increment('cash_balance', $amount);

            return $this->record($game, [
                'type' => 'withdraw',
                'amount' => $amount,
                'from_player_id' => $player->id,
                'description' => "{$player->name} withdraw cash {$amount}" . ($reason ? " ({$reason})" : ''),
                'balance_after_from' => $player->fresh()->balance,
            ]);
        });
    }

    public function bankToPlayer(Game $game, int $playerId, int $amount, ?string $reason = null, ?string $cardKey = null): Transaction
    {
        return DB::transaction(function () use ($game, $playerId, $amount, $reason, $cardKey) {
            $this->ensureActive($game);
            $amount = $this->positiveAmount($amount);
            $this->ensureCardAvailable($game, $cardKey);
            $player = $this->playerForUpdate($game, $playerId);
            $player->increment('balance', $amount);

            return $this->record($game, [
                'type' => 'bank_to_player',
                'amount' => $amount,
                'to_player_id' => $player->id,
                'description' => "Bank memberi {$amount} ke {$player->name}" . ($reason ? " ({$reason})" : ''),
                'balance_after_to' => $player->fresh()->balance,
                'meta' => ['card_key' => $cardKey],
            ]);
        });
    }

    public function playerToBank(Game $game, int $playerId, int $amount, ?string $reason = null, ?string $cardKey = null): Transaction
    {
        return DB::transaction(function () use ($game, $playerId, $amount, $reason, $cardKey) {
            $this->ensureActive($game);
            $amount = $this->positiveAmount($amount);
            $this->ensureCardAvailable($game, $cardKey);
            $player = $this->playerForUpdate($game, $playerId);
            $this->ensureFunds($player, $amount);
            $player->decrement('balance', $amount);

            return $this->record($game, [
                'type' => 'player_to_bank',
                'amount' => $amount,
                'from_player_id' => $player->id,
                'description' => "{$player->name} bayar {$amount} ke Bank" . ($reason ? " ({$reason})" : ''),
                'balance_after_from' => $player->fresh()->balance,
                'meta' => ['card_key' => $cardKey],
            ]);
        });
    }

    public function buyProperty(Game $game, int $playerId, int $gamePropertyId): Transaction
    {
        return DB::transaction(function () use ($game, $playerId, $gamePropertyId) {
            $this->ensureActive($game);
            $player = $this->playerForUpdate($game, $playerId);
            $gameProperty = $this->gamePropertyForUpdate($game, $gamePropertyId);

            if ($gameProperty->owner_id) {
                throw ValidationException::withMessages(['property' => 'Properti ini sudah dimiliki pemain lain.']);
            }

            $amount = intdiv($gameProperty->property->price, 2);
            $this->ensureFunds($player, $amount);
            $player->decrement('balance', $amount);
            $gameProperty->update(['owner_id' => $player->id]);

            return $this->record($game, [
                'type' => 'buy_property',
                'amount' => $amount,
                'from_player_id' => $player->id,
                'game_property_id' => $gameProperty->id,
                'description' => "{$player->name} membeli {$gameProperty->property->name}",
                'balance_after_from' => $player->fresh()->balance,
            ]);
        });
    }

    public function sellProperty(Game $game, int $playerId, int $gamePropertyId): Transaction
    {
        return DB::transaction(function () use ($game, $playerId, $gamePropertyId) {
            $this->ensureActive($game);
            $player = $this->playerForUpdate($game, $playerId);
            $gameProperty = $this->ownedGamePropertyForUpdate($game, $gamePropertyId, $player->id);
            $amount = $gameProperty->property->price;

            $player->increment('balance', $amount);
            $gameProperty->update([
                'owner_id' => null,
                'house_count' => 0,
                'has_hotel' => false,
                'is_mortgaged' => false,
            ]);

            return $this->record($game, [
                'type' => 'sell_property',
                'amount' => $amount,
                'to_player_id' => $player->id,
                'game_property_id' => $gameProperty->id,
                'description' => "{$player->name} menjual {$gameProperty->property->name} ke Bank setengah harga",
                'balance_after_to' => $player->fresh()->balance,
            ]);
        });
    }

    public function transferProperty(Game $game, int $fromPlayerId, int $toPlayerId, int $gamePropertyId): Transaction
    {
        return DB::transaction(function () use ($game, $fromPlayerId, $toPlayerId, $gamePropertyId) {
            $this->ensureActive($game);
            $this->ensureDifferentPlayers($fromPlayerId, $toPlayerId);
            $from = $this->playerForUpdate($game, $fromPlayerId);
            $to = $this->playerForUpdate($game, $toPlayerId);
            $gameProperty = $this->ownedGamePropertyForUpdate($game, $gamePropertyId, $from->id);
            $gameProperty->update(['owner_id' => $to->id]);

            return $this->record($game, [
                'type' => 'transfer_property',
                'from_player_id' => $from->id,
                'to_player_id' => $to->id,
                'game_property_id' => $gameProperty->id,
                'description' => "{$from->name} transfer {$gameProperty->property->name} ke {$to->name}",
            ]);
        });
    }

    public function addHouse(Game $game, int $playerId, int $gamePropertyId): Transaction
    {
        return DB::transaction(function () use ($game, $playerId, $gamePropertyId) {
            $this->ensureActive($game);
            $player = $this->playerForUpdate($game, $playerId);
            $gameProperty = $this->ownedGamePropertyForUpdate($game, $gamePropertyId, $player->id);

            if ($gameProperty->has_hotel || $gameProperty->house_count >= 4) {
                throw ValidationException::withMessages(['house' => 'Maksimal 4 rumah sebelum membeli hotel.']);
            }

            $amount = $gameProperty->property->house_price;
            $this->ensureFunds($player, $amount);
            $player->decrement('balance', $amount);
            $gameProperty->increment('house_count');

            return $this->record($game, [
                'type' => 'add_house',
                'amount' => $amount,
                'from_player_id' => $player->id,
                'game_property_id' => $gameProperty->id,
                'description' => "{$player->name} menambah rumah di {$gameProperty->property->name}",
                'balance_after_from' => $player->fresh()->balance,
            ]);
        });
    }

    public function sellHouse(Game $game, int $playerId, int $gamePropertyId): Transaction
    {
        return DB::transaction(function () use ($game, $playerId, $gamePropertyId) {
            $this->ensureActive($game);
            $player = $this->playerForUpdate($game, $playerId);
            $gameProperty = $this->ownedGamePropertyForUpdate($game, $gamePropertyId, $player->id);

            if ($gameProperty->has_hotel || $gameProperty->house_count < 1) {
                throw ValidationException::withMessages(['house' => 'Properti ini tidak memiliki rumah yang bisa dijual.']);
            }

            $amount = intdiv($gameProperty->property->house_price, 2);
            $player->increment('balance', $amount);
            $gameProperty->decrement('house_count');

            return $this->record($game, [
                'type' => 'sell_house',
                'amount' => $amount,
                'to_player_id' => $player->id,
                'game_property_id' => $gameProperty->id,
                'description' => "{$player->name} menjual 1 rumah di {$gameProperty->property->name}",
                'balance_after_to' => $player->fresh()->balance,
            ]);
        });
    }

    public function addHotel(Game $game, int $playerId, int $gamePropertyId): Transaction
    {
        return DB::transaction(function () use ($game, $playerId, $gamePropertyId) {
            $this->ensureActive($game);
            $player = $this->playerForUpdate($game, $playerId);
            $gameProperty = $this->ownedGamePropertyForUpdate($game, $gamePropertyId, $player->id);

            if ($gameProperty->has_hotel) {
                throw ValidationException::withMessages(['hotel' => 'Properti ini sudah memiliki hotel.']);
            }

            $amount = $gameProperty->property->hotel_price;
            $houseRefund = $gameProperty->house_count * intdiv($gameProperty->property->house_price, 2);
            if (($player->balance + $houseRefund) < $amount) {
                throw ValidationException::withMessages(['balance' => "{$player->name} tidak memiliki saldo cukup."]);
            }
            if ($houseRefund > 0) {
                $player->increment('balance', $houseRefund);
            }
            $player->decrement('balance', $amount);
            $gameProperty->update([
                'house_count' => 0,
                'has_hotel' => true,
            ]);

            return $this->record($game, [
                'type' => 'add_hotel',
                'amount' => $amount,
                'from_player_id' => $player->id,
                'game_property_id' => $gameProperty->id,
                'description' => "{$player->name} menambah hotel di {$gameProperty->property->name}" . ($houseRefund > 0 ? " dan menjual rumah {$houseRefund}" : ''),
                'balance_after_from' => $player->fresh()->balance,
                'meta' => ['house_refund' => $houseRefund],
            ]);
        });
    }

    public function sellHotel(Game $game, int $playerId, int $gamePropertyId): Transaction
    {
        return DB::transaction(function () use ($game, $playerId, $gamePropertyId) {
            $this->ensureActive($game);
            $player = $this->playerForUpdate($game, $playerId);
            $gameProperty = $this->ownedGamePropertyForUpdate($game, $gamePropertyId, $player->id);

            if (! $gameProperty->has_hotel) {
                throw ValidationException::withMessages(['hotel' => 'Properti ini tidak memiliki hotel yang bisa dijual.']);
            }

            $amount = intdiv($gameProperty->property->hotel_price, 2);
            $player->increment('balance', $amount);
            $gameProperty->update(['has_hotel' => false]);

            return $this->record($game, [
                'type' => 'sell_hotel',
                'amount' => $amount,
                'to_player_id' => $player->id,
                'game_property_id' => $gameProperty->id,
                'description' => "{$player->name} menjual hotel di {$gameProperty->property->name}",
                'balance_after_to' => $player->fresh()->balance,
            ]);
        });
    }

    public function auctionProperty(Game $game, int $winnerPlayerId, int $gamePropertyId, int $amount): Transaction
    {
        return DB::transaction(function () use ($game, $winnerPlayerId, $gamePropertyId, $amount) {
            $this->ensureActive($game);
            $amount = $this->positiveAmount($amount);
            $winner = $this->playerForUpdate($game, $winnerPlayerId);
            $gameProperty = $this->gamePropertyForUpdate($game, $gamePropertyId);

            if ($gameProperty->owner_id) {
                throw ValidationException::withMessages(['property' => 'Properti yang dilelang harus belum dimiliki.']);
            }

            $this->ensureFunds($winner, $amount);
            $winner->decrement('balance', $amount);
            $gameProperty->update(['owner_id' => $winner->id]);

            return $this->record($game, [
                'type' => 'auction_property',
                'amount' => $amount,
                'from_player_id' => $winner->id,
                'game_property_id' => $gameProperty->id,
                'description' => "{$winner->name} memenangkan lelang {$gameProperty->property->name}",
                'balance_after_from' => $winner->fresh()->balance,
            ]);
        });
    }

    private function generateGameCode(): string
    {
        $next = (Game::query()->max('id') ?? 0) + 1;

        return 'MB-' . now()->format('Ymd') . '-' . str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    private function normalizeUid(?string $uid): ?string
    {
        $uid = Str::upper(Str::replace(' ', '', trim((string) $uid)));

        return $uid === '' ? null : $uid;
    }

    private function record(Game $game, array $attributes): Transaction
    {
        return Transaction::query()->create(array_merge([
            'game_id' => $game->id,
            'amount' => 0,
            'description' => 'Transaksi game',
        ], $attributes));
    }

    private function ensureActive(Game $game): void
    {
        $game->refresh();

        if ($game->status === 'paused') {
            throw ValidationException::withMessages(['game' => 'Game sedang pause. Resume sebelum transaksi.']);
        }

        $this->ensureNotFinished($game);
    }

    private function ensureNotFinished(Game $game): void
    {
        $game->refresh();

        if ($game->status === 'finished') {
            throw ValidationException::withMessages(['game' => 'Game sudah selesai.']);
        }
    }

    private function ensureDifferentPlayers(int $firstPlayerId, int $secondPlayerId): void
    {
        if ($firstPlayerId === $secondPlayerId) {
            throw ValidationException::withMessages(['player' => 'Pilih dua pemain yang berbeda.']);
        }
    }

    private function positiveAmount(int $amount): int
    {
        if ($amount <= 0) {
            throw ValidationException::withMessages(['amount' => 'Nominal harus lebih dari 0.']);
        }

        return $amount;
    }

    private function ensureFunds(Player $player, int $amount): void
    {
        if ($player->balance < $amount) {
            throw ValidationException::withMessages(['balance' => "{$player->name} tidak memiliki saldo cukup."]);
        }
    }

    private function ensureCashFunds(Player $player, int $amount): void
    {
        if ($player->cash_balance < $amount) {
            throw ValidationException::withMessages(['cash_balance' => "{$player->name} tidak memiliki cash fisik cukup."]);
        }
    }

    private function ensureCardAvailable(Game $game, ?string $cardKey): void
    {
        if (! $cardKey) {
            return;
        }

        if (in_array($cardKey, $this->usedCardKeys($game), true)) {
            throw ValidationException::withMessages(['card' => 'Kartu ini sudah dipakai. Klik Refresh Kartu untuk membuka semua kartu lagi.']);
        }
    }

    private function bankruptcySummary(Player $player, EloquentCollection $gameProperties): array
    {
        $ownedProperties = $gameProperties->where('owner_id', $player->id);
        $propertySale = $ownedProperties->sum(fn (GameProperty $gameProperty) => intdiv($gameProperty->property->price, 2));
        $houseSale = $ownedProperties->sum(fn (GameProperty $gameProperty) => $gameProperty->house_count * intdiv($gameProperty->property->house_price, 2));
        $hotelSale = $ownedProperties->sum(fn (GameProperty $gameProperty) => $gameProperty->has_hotel ? intdiv($gameProperty->property->hotel_price, 2) : 0);

        return [
            'property_count' => $ownedProperties->count(),
            'house_count' => $ownedProperties->sum('house_count'),
            'hotel_count' => $ownedProperties->where('has_hotel', true)->count(),
            'property_sale' => $propertySale,
            'house_sale' => $houseSale,
            'hotel_sale' => $hotelSale,
            'sale_total' => $propertySale + $houseSale + $hotelSale,
            'liquid_balance' => $player->balance + $player->cash_balance,
            'total_liquidation' => $player->balance + $player->cash_balance + $propertySale + $houseSale + $hotelSale,
        ];
    }

    private function validateRentTarget(GameProperty $property, Player $payer): void
    {
        if (! $property->owner_id) {
            throw ValidationException::withMessages(['property' => "{$property->property->name} belum dimiliki pemain."]);
        }

        if ((int) $property->owner_id === (int) $payer->id) {
            throw ValidationException::withMessages(['property' => 'Tidak perlu bayar sewa ke tanah sendiri.']);
        }
    }

    private function ensureRentNotRecentlyPaid(Game $game, Player $payer, GameProperty $property): void
    {
        $recentDuplicate = Transaction::query()
            ->where('game_id', $game->id)
            ->where('from_player_id', $payer->id)
            ->where('game_property_id', $property->id)
            ->whereIn('type', ['pay_rent', 'rent_bankruptcy'])
            ->where('created_at', '>=', now()->subSeconds(10))
            ->exists();

        if ($recentDuplicate) {
            throw ValidationException::withMessages(['rent' => 'Sewa ini baru saja dibayar. Tunggu sebentar supaya tidak dobel bayar.']);
        }
    }

    private function boardSpace(int $position): array
    {
        return self::BOARD_SPACES[$position % count(self::BOARD_SPACES)] ?? self::BOARD_SPACES[0];
    }

    private function movePlayerByDice(Game $game, Player $player, int $steps): array
    {
        $boardSize = count(self::BOARD_SPACES);
        $oldPosition = (int) $player->board_position;
        $rawPosition = $oldPosition + $steps;
        $newPosition = $rawPosition % $boardSize;
        $passedStart = $rawPosition >= $boardSize;
        $landedOnStart = $newPosition === 0;
        $lapCount = (int) $player->lap_count + ($passedStart ? intdiv($rawPosition, $boardSize) : 0);
        $rulesUnlocked = $player->rules_unlocked || $lapCount > 0;
        $space = $this->boardSpace($newPosition);

        $player->update([
            'board_position' => $newPosition,
            'lap_count' => $lapCount,
            'rules_unlocked' => $rulesUnlocked,
            'pending_space_action' => null,
        ]);
        $player->refresh();

        $bonus = 0;
        if ($passedStart) {
            $bonus += 20000;
        } elseif ($landedOnStart) {
            $bonus += 10000;
        }

        if ($bonus > 0) {
            $player->increment('balance', $bonus);
            $this->record($game, [
                'type' => $landedOnStart && ! $passedStart ? 'start_bonus_half' : 'start_bonus',
                'amount' => $bonus,
                'to_player_id' => $player->id,
                'description' => $landedOnStart && ! $passedStart
                    ? "{$player->name} berhenti tepat di Start dan menerima 10000"
                    : "{$player->name} melewati Start dan menerima 20000",
                'balance_after_to' => $player->fresh()->balance,
                'meta' => [
                    'from_position' => $oldPosition,
                    'to_position' => $newPosition,
                    'space' => $space,
                ],
            ]);
        }

        $action = $rulesUnlocked
            ? $this->spaceActionFor($game, $player->fresh(), $space)
            : $this->lockedSpaceAction($space);

        if (($action['action'] ?? 'none') === 'go_to_jail') {
            $player->update([
                'board_position' => 10,
                'is_in_jail' => true,
                'jail_turn_count' => 0,
                'pending_jail_release' => false,
                'pending_space_action' => null,
                'double_streak' => 0,
            ]);
        } elseif (($action['requires_resolution'] ?? false) || ($action['action'] ?? null) === 'info') {
            $player->update(['pending_space_action' => $action]);
        }

        $this->record($game, [
            'type' => 'board_move',
            'to_player_id' => $player->id,
            'amount' => $steps,
            'description' => "{$player->name} berjalan {$steps} langkah ke {$space['name']}",
            'meta' => [
                'from_position' => $oldPosition,
                'to_position' => $newPosition,
                'space' => $space,
                'rules_unlocked' => $rulesUnlocked,
                'action' => $action,
            ],
        ]);

        return [
            'from_position' => $oldPosition,
            'to_position' => $newPosition,
            'from_space' => $this->boardSpace($oldPosition),
            'to_space' => $space,
            'passed_start' => $passedStart,
            'landed_on_start' => $landedOnStart,
            'start_bonus' => $bonus,
            'lap_count' => $lapCount,
            'rules_unlocked' => $rulesUnlocked,
            'action' => $action,
        ];
    }

    private function lockedSpaceAction(array $space): array
    {
        return [
            'action' => 'locked_intro_lap',
            'space_index' => $space['index'],
            'space_name' => $space['name'],
            'space_type' => $space['type'],
            'label' => 'Putaran awal',
            'message' => 'Aturan belum aktif sampai pemain melewati Start pertama kali.',
            'requires_resolution' => false,
        ];
    }

    private function spaceActionFor(Game $game, Player $player, array $space): array
    {
        return match ($space['type']) {
            'start' => [
                'action' => 'none',
                'space_index' => $space['index'],
                'space_name' => $space['name'],
                'space_type' => $space['type'],
                'label' => 'Start',
                'message' => 'Bonus Start sudah otomatis masuk bila berlaku.',
                'requires_resolution' => false,
            ],
            'tax' => $this->paymentSpaceAction($space, 'pay_tax', 'Bayar Pajak Jalan', 20000),
            'special_tax' => $this->paymentSpaceAction($space, 'pay_special_tax', 'Bayar Pajak Istimewa', 10000),
            'property', 'transport', 'utility' => $this->propertySpaceAction($game, $player, $space),
            'go_to_jail' => [
                'action' => 'go_to_jail',
                'space_index' => $space['index'],
                'space_name' => $space['name'],
                'space_type' => $space['type'],
                'label' => 'Masuk Penjara',
                'message' => 'Pemain langsung masuk penjara.',
                'requires_resolution' => false,
            ],
            'community_card', 'chance_card' => [
                'action' => 'info',
                'space_index' => $space['index'],
                'space_name' => $space['name'],
                'space_type' => $space['type'],
                'label' => $space['type'] === 'community_card' ? 'Dana Umum' : 'Kesempatan',
                'message' => 'Ambil kartu fisik/manual dulu. Efek kartu online akan dibuat di update berikutnya.',
                'requires_resolution' => true,
            ],
            default => [
                'action' => 'none',
                'space_index' => $space['index'],
                'space_name' => $space['name'],
                'space_type' => $space['type'],
                'label' => 'Tidak ada aksi',
                'message' => 'Tidak ada pembayaran di petak ini.',
                'requires_resolution' => false,
            ],
        };
    }

    private function paymentSpaceAction(array $space, string $action, string $label, int $amount): array
    {
        return [
            'action' => $action,
            'space_index' => $space['index'],
            'space_name' => $space['name'],
            'space_type' => $space['type'],
            'label' => $label,
            'message' => "{$label} sebesar {$amount}.",
            'amount' => $amount,
            'requires_resolution' => true,
        ];
    }

    private function propertySpaceAction(Game $game, Player $player, array $space): array
    {
        $property = $this->gamePropertyForSpace($game, $space);

        if (! $property) {
            return [
                'action' => 'info',
                'space_index' => $space['index'],
                'space_name' => $space['name'],
                'space_type' => $space['type'],
                'label' => 'Data properti belum cocok',
                'message' => "Petak {$space['name']} belum cocok dengan database properti.",
                'requires_resolution' => true,
            ];
        }

        if (! $property->owner_id) {
            return [
                'action' => 'buy_property',
                'space_index' => $space['index'],
                'space_name' => $space['name'],
                'space_type' => $space['type'],
                'game_property_id' => $property->id,
                'property_name' => $property->property->name,
                'amount' => $property->property->price,
                'label' => "Beli {$property->property->name}",
                'message' => "{$property->property->name} belum dimiliki. Beli, lewati, atau lelang.",
                'requires_resolution' => true,
            ];
        }

        if ((int) $property->owner_id === (int) $player->id) {
            return [
                'action' => 'own_property',
                'space_index' => $space['index'],
                'space_name' => $space['name'],
                'space_type' => $space['type'],
                'game_property_id' => $property->id,
                'property_name' => $property->property->name,
                'label' => 'Properti sendiri',
                'message' => "{$property->property->name} adalah properti kamu.",
                'requires_resolution' => false,
            ];
        }

        $rent = $this->rentDetails($game, $property, $player);

        return [
            'action' => 'pay_rent',
            'space_index' => $space['index'],
            'space_name' => $space['name'],
            'space_type' => $space['type'],
            'game_property_id' => $property->id,
            'property_name' => $property->property->name,
            'owner_id' => $property->owner_id,
            'owner_name' => $property->owner?->name,
            'amount' => $rent['amount'],
            'label' => "Bayar sewa {$property->property->name}",
            'message' => "Bayar sewa {$rent['amount']} ke {$property->owner?->name}.",
            'rent' => $rent,
            'requires_resolution' => true,
        ];
    }

    private function gamePropertyForSpace(Game $game, array $space): ?GameProperty
    {
        $name = Str::lower($space['name']);
        $aliases = [
            'terminal tokyo' => 'terminal bus tokyo',
            'rrc (china)' => 'rrc',
            'brazilia' => 'brazilia',
        ];
        $lookupName = $aliases[$name] ?? $name;

        return $game->gameProperties()
            ->with(['property', 'owner'])
            ->get()
            ->first(function (GameProperty $gameProperty) use ($name, $lookupName) {
                $propertyName = Str::lower($gameProperty->property->name);

                return $propertyName === $name
                    || $propertyName === $lookupName
                    || str_contains($propertyName, $lookupName)
                    || str_contains($lookupName, $propertyName);
            });
    }

    private function buildTurnOrder(Game $game, int $firstPlayerId): array
    {
        $players = $game->players()
            ->where('is_bankrupt', false)
            ->orderBy('sort_order')
            ->pluck('id')
            ->values()
            ->all();

        $firstIndex = array_search($firstPlayerId, $players, true);
        if ($firstIndex === false) {
            return $players;
        }

        return array_values(array_merge(
            array_slice($players, $firstIndex),
            array_slice($players, 0, $firstIndex),
        ));
    }

    private function advanceTurn(Game $game, Player $currentPlayer): void
    {
        $order = $game->turn_order ?: $this->buildTurnOrder($game, $game->first_player_id ?: $currentPlayer->id);
        $activeIds = Player::query()
            ->where('game_id', $game->id)
            ->where('is_bankrupt', false)
            ->orderBy('sort_order')
            ->pluck('id')
            ->all();
        $order = array_values(array_filter($order, fn ($id) => in_array($id, $activeIds, true)));

        if ($order === []) {
            $game->update(['current_turn_player_id' => null]);
            return;
        }

        $currentIndex = array_search($currentPlayer->id, $order, true);
        $nextIndex = $currentIndex === false ? 0 : ($currentIndex + 1) % count($order);

        $game->update([
            'turn_order' => $order,
            'current_turn_player_id' => $order[$nextIndex],
            'turn_number' => ($game->turn_number ?: 1) + 1,
        ]);
    }

    private function rentDetails(Game $game, GameProperty $property, Player $payer): array
    {
        $completeGroups = $this->completeGroups($game);
        $ownerCompleteGroup = in_array($property->property->group_code, $completeGroups[$property->owner_id] ?? [], true);
        $payerCompleteGroupCount = count($completeGroups[$payer->id] ?? []);
        $multiplier = 1;
        $rule = 'normal';
        $ruleLabel = '';

        if ($property->property->property_kind === 'utility') {
            $multiplier = $payerCompleteGroupCount >= 2 ? 10 : ($payerCompleteGroupCount === 1 ? 4 : 1);
            $rule = 'utility_by_payer_complex';
            $ruleLabel = $multiplier > 1
                ? "{$payerCompleteGroupCount} komplek penuh milik {$payer->name} x{$multiplier}"
                : "{$payer->name} belum punya komplek penuh";
            $baseRent = 7500;
            $amount = $baseRent * $multiplier;
        } else {
            $amount = $this->currentRent($property, $ownerCompleteGroup);
            $multiplier = $ownerCompleteGroup ? 2 : 1;
            $ruleLabel = $ownerCompleteGroup ? 'komplek lengkap x2' : '';
        }

        return [
            'amount' => $amount,
            'base_rent' => $property->property->property_kind === 'utility' ? 7500 : $this->baseRent($property),
            'multiplier' => $multiplier,
            'rule' => $rule,
            'rule_label' => $ruleLabel,
            'owner_id' => $property->owner_id,
            'owner_name' => $property->owner?->name,
            'property_id' => $property->id,
            'property_name' => $property->property->name,
            'property_kind' => $property->property->property_kind,
            'owner_complete_group' => $ownerCompleteGroup,
            'payer_complete_group_count' => $payerCompleteGroupCount,
            'house_count' => $property->house_count,
            'has_hotel' => $property->has_hotel,
        ];
    }

    private function baseRent(GameProperty $gameProperty): int
    {
        if ($gameProperty->has_hotel) {
            return $gameProperty->property->rent_hotel;
        }

        return match ($gameProperty->house_count) {
            1 => $gameProperty->property->rent_1_house,
            2 => $gameProperty->property->rent_2_houses,
            3 => $gameProperty->property->rent_3_houses,
            4 => $gameProperty->property->rent_4_houses,
            default => $gameProperty->property->rent,
        };
    }

    private function bankruptForRent(Game $game, Player $payer, Player $owner, GameProperty $property, array $rent, array $bankruptcy): Transaction
    {
        $gameProperties = GameProperty::query()
            ->with('property')
            ->where('game_id', $game->id)
            ->where('owner_id', $payer->id)
            ->lockForUpdate()
            ->get();

        $payable = $payer->balance + $payer->cash_balance + $bankruptcy['sale_total'];

        $gameProperties->each(fn (GameProperty $gameProperty) => $gameProperty->update([
            'owner_id' => null,
            'house_count' => 0,
            'has_hotel' => false,
            'is_mortgaged' => false,
        ]));

        $payer->update([
            'balance' => 0,
            'cash_balance' => 0,
            'is_bankrupt' => true,
            'bankrupted_at' => now(),
            'bankrupt_summary' => $bankruptcy + [
                'reason' => 'rent',
                'owed_amount' => $rent['amount'],
                'paid_to_owner' => $payable,
                'property_name' => $property->property->name,
                'owner_name' => $owner->name,
            ],
        ]);
        $owner->increment('balance', $payable);

        if ((int) $game->last_scanned_player_id === (int) $payer->id) {
            $game->update(['last_scanned_player_id' => null]);
        }

        if ((int) $game->current_turn_player_id === (int) $payer->id) {
            $this->advanceTurn($game, $payer);
        }

        return $this->record($game, [
            'type' => 'rent_bankruptcy',
            'amount' => $payable,
            'from_player_id' => $payer->id,
            'to_player_id' => $owner->id,
            'game_property_id' => $property->id,
            'description' => "{$payer->name} bangkrut saat bayar sewa {$property->property->name}. Semua sisa uang dan hasil jual aset {$payable} dibayarkan ke {$owner->name}",
            'balance_after_from' => 0,
            'balance_after_to' => $owner->fresh()->balance,
            'meta' => [
                'rent' => $rent,
                'bankruptcy' => $bankruptcy,
                'cash_after_from' => 0,
                'cash_after_to' => $owner->fresh()->cash_balance,
            ],
        ]);
    }

    private function playerForUpdate(Game $game, int $playerId): Player
    {
        $player = Player::query()
            ->where('game_id', $game->id)
            ->whereKey($playerId)
            ->lockForUpdate()
            ->first();

        if (! $player) {
            throw ValidationException::withMessages(['player' => 'Pemain tidak ditemukan pada game ini.']);
        }

        if ($player->is_bankrupt) {
            throw ValidationException::withMessages(['player' => "{$player->name} sudah bangkrut dan tidak bisa transaksi."]);
        }

        return $player;
    }

    private function gamePropertyForUpdate(Game $game, int $gamePropertyId): GameProperty
    {
        $gameProperty = GameProperty::query()
            ->with(['property', 'owner'])
            ->where('game_id', $game->id)
            ->whereKey($gamePropertyId)
            ->lockForUpdate()
            ->first();

        if (! $gameProperty) {
            throw ValidationException::withMessages(['property' => 'Properti tidak ditemukan pada game ini.']);
        }

        return $gameProperty;
    }

    private function ownedGamePropertyForUpdate(Game $game, int $gamePropertyId, int $ownerId): GameProperty
    {
        $gameProperty = $this->gamePropertyForUpdate($game, $gamePropertyId);

        if ((int) $gameProperty->owner_id !== $ownerId) {
            throw ValidationException::withMessages(['property' => 'Properti ini tidak dimiliki pemain yang dipilih.']);
        }

        return $gameProperty;
    }

    private function wealthRows(Game $game): array
    {
        return $game->players->mapWithKeys(function (Player $player) use ($game) {
            $ownedProperties = $game->gameProperties->where('owner_id', $player->id);
            $propertyValue = $ownedProperties->sum(fn (GameProperty $gameProperty) => $gameProperty->property->price);
            $houseValue = $ownedProperties->sum(fn (GameProperty $gameProperty) => $gameProperty->house_count * $gameProperty->property->house_price);
            $hotelValue = $ownedProperties->sum(fn (GameProperty $gameProperty) => $gameProperty->has_hotel ? $gameProperty->property->hotel_price : 0);
            $liquidValue = $player->balance + $player->cash_balance;
            $totalAsset = $player->is_bankrupt ? 0 : $liquidValue + $propertyValue + $houseValue + $hotelValue;

            return [$player->id => [
                'liquid_value' => $liquidValue,
                'property_value' => $propertyValue,
                'house_value' => $houseValue,
                'hotel_value' => $hotelValue,
                'total_asset' => $totalAsset,
            ]];
        })->all();
    }

    private function playerPayload(Player $player, EloquentCollection $gameProperties, ?array $wealth): array
    {
        $ownedProperties = $gameProperties->where('owner_id', $player->id);

        return [
            'id' => $player->id,
            'name' => $player->name,
            'rfid_uid' => $player->rfid_uid,
            'avatar_color' => $player->avatar_color,
            'balance' => $player->balance,
            'cash_balance' => $player->cash_balance,
            'is_bankrupt' => $player->is_bankrupt,
            'is_in_jail' => $player->is_in_jail,
            'double_streak' => $player->double_streak,
            'jail_turn_count' => $player->jail_turn_count,
            'pending_jail_release' => $player->pending_jail_release,
            'jail_free_cards' => $player->jail_free_cards,
            'board_position' => $player->board_position,
            'lap_count' => $player->lap_count,
            'rules_unlocked' => $player->rules_unlocked,
            'current_space' => $this->boardSpace($player->board_position),
            'pending_space_action' => $player->pending_space_action,
            'bankrupted_at' => $player->bankrupted_at?->toIso8601String(),
            'bankrupt_summary' => $player->bankrupt_summary,
            'liquid_value' => $wealth['liquid_value'] ?? ($player->balance + $player->cash_balance),
            'property_count' => $ownedProperties->count(),
            'house_count' => $ownedProperties->sum('house_count'),
            'hotel_count' => $ownedProperties->where('has_hotel', true)->count(),
            'property_value' => $wealth['property_value'] ?? 0,
            'house_value' => $wealth['house_value'] ?? 0,
            'hotel_value' => $wealth['hotel_value'] ?? 0,
            'total_asset' => $wealth['total_asset'] ?? ($player->balance + $player->cash_balance),
            'properties' => $ownedProperties
                ->sortBy('property.sort_order')
                ->map(fn (GameProperty $gameProperty) => [
                    'id' => $gameProperty->id,
                    'name' => $gameProperty->property->name,
                    'color' => $gameProperty->property->color,
                    'group_code' => $gameProperty->property->group_code,
                    'group_name' => $gameProperty->property->group_name,
                    'property_kind' => $gameProperty->property->property_kind,
                    'price' => $gameProperty->property->price,
                    'house_price' => $gameProperty->property->house_price,
                    'hotel_price' => $gameProperty->property->hotel_price,
                    'current_rent' => $this->currentRent($gameProperty, false),
                    'house_count' => $gameProperty->house_count,
                    'has_hotel' => $gameProperty->has_hotel,
                ])
                ->values()
                ->all(),
            'history' => [
                'transactions' => Transaction::query()
                    ->where('game_id', $player->game_id)
                    ->where(fn ($query) => $query
                        ->where('from_player_id', $player->id)
                        ->orWhere('to_player_id', $player->id))
                    ->latest()
                    ->take(30)
                    ->get()
                    ->map(fn (Transaction $transaction) => [
                        'id' => $transaction->id,
                        'type' => $transaction->type,
                        'amount' => $transaction->amount,
                        'description' => $transaction->description,
                        'created_at_label' => $transaction->created_at?->format('H:i:s'),
                    ])
                    ->values()
                    ->all(),
                'ever_owned_properties' => Transaction::query()
                    ->where('game_id', $player->game_id)
                    ->where('to_player_id', $player->id)
                    ->whereIn('type', ['buy_property', 'auction_property', 'transfer_property'])
                    ->whereNotNull('game_property_id')
                    ->with('gameProperty.property')
                    ->get()
                    ->map(fn (Transaction $transaction) => $transaction->gameProperty?->property?->name)
                    ->filter()
                    ->unique()
                    ->values()
                    ->all(),
            ],
        ];
    }

    private function playerPortalLinks(Game $game): array
    {
        return $game->playerAccessTokens
            ->sortBy('player.sort_order')
            ->map(fn (PlayerAccessToken $token) => [
                'player_id' => $token->player_id,
                'player_name' => $token->player?->name,
                'token' => $token->token,
                'last_seen_at' => $token->last_seen_at?->toIso8601String(),
                'last_seen_label' => $token->last_seen_at?->format('H:i:s'),
                'revoked_at' => $token->revoked_at?->toIso8601String(),
                'status' => $token->last_seen_at ? 'Terhubung' : 'Belum dibuka',
            ])
            ->values()
            ->all();
    }

    private function transactionRequestPayload(TransactionRequest $request): array
    {
        return [
            'id' => $request->id,
            'type' => $request->type,
            'type_label' => Str::headline(str_replace('_', ' ', $request->type)),
            'player_id' => $request->player_id,
            'player_name' => $request->player?->name,
            'target_player_id' => $request->target_player_id,
            'target_player_name' => $request->targetPlayer?->name,
            'game_property_id' => $request->game_property_id,
            'property_name' => $request->gameProperty?->property?->name,
            'amount' => $request->amount,
            'source' => $request->source,
            'reason' => $request->reason,
            'status' => $request->status,
            'created_at_label' => $request->created_at?->format('H:i:s'),
        ];
    }

    private function requestDescription(Player $player, string $type, ?GameProperty $property, ?Player $target, array $payload): string
    {
        $propertyName = $property?->property?->name ?? 'pilihan tanah';
        $amount = (int) ($payload['amount'] ?? 0);

        return match ($type) {
            'buy_property' => "{$player->name} ingin membeli {$propertyName}",
            'sell_property' => "{$player->name} ingin menjual {$propertyName}",
            'add_house' => "{$player->name} ingin membeli rumah di {$propertyName}",
            'sell_house' => "{$player->name} ingin menjual rumah di {$propertyName}",
            'add_hotel' => "{$player->name} ingin membeli hotel di {$propertyName}",
            'sell_hotel' => "{$player->name} ingin menjual hotel di {$propertyName}",
            'pay_rent' => "{$player->name} ingin membayar sewa {$propertyName}",
            'transfer' => "{$player->name} ingin mengirim {$amount} ke {$target?->name}",
            'deposit' => "{$player->name} ingin setor tunai {$amount}",
            'withdraw' => "{$player->name} ingin tarik tunai {$amount}",
            'player_to_bank' => "{$player->name} ingin membayar {$amount} ke Bank",
            'bank_to_player' => "{$player->name} meminta uang {$amount} dari Bank",
            'pay_jail_fee' => "{$player->name} ingin bayar 5000 untuk bebas penjara",
            'use_jail_card' => "{$player->name} ingin memakai kartu bebas penjara",
            default => "{$player->name} mengajukan transaksi",
        };
    }

    private function validatePlayerRequest(Player $player, string $type, ?GameProperty $property, ?Player $target, array $payload): void
    {
        if (in_array($type, ['buy_property', 'sell_property', 'add_house', 'sell_house', 'add_hotel', 'sell_hotel', 'pay_rent'], true) && ! $property) {
            throw ValidationException::withMessages(['property' => 'Pilih tanah/tempat dulu.']);
        }

        if ($type === 'buy_property' && $property?->owner_id) {
            throw ValidationException::withMessages(['property' => "{$property->property->name} sudah dimiliki {$property->owner?->name}."]);
        }

        if (in_array($type, ['sell_property', 'add_house', 'sell_house', 'add_hotel', 'sell_hotel'], true) && (int) $property?->owner_id !== (int) $player->id) {
            throw ValidationException::withMessages(['property' => 'Kamu hanya bisa mengajukan bangunan atau jual tanah milikmu sendiri.']);
        }

        if ($type === 'pay_rent') {
            if (! $property?->owner_id) {
                throw ValidationException::withMessages(['property' => "{$property->property->name} belum ada pemiliknya, jadi belum perlu bayar sewa."]);
            }

            if ((int) $property->owner_id === (int) $player->id) {
                throw ValidationException::withMessages(['property' => "Kamu pemilik {$property->property->name}, jadi tidak perlu bayar sewa ke diri sendiri."]);
            }
        }

        if ($type === 'transfer') {
            if (! $target) {
                throw ValidationException::withMessages(['target_player_id' => 'Pilih pemain penerima dulu.']);
            }

            if ((int) $target->id === (int) $player->id) {
                throw ValidationException::withMessages(['target_player_id' => 'Kamu tidak perlu kirim uang ke diri sendiri.']);
            }
        }

        if (in_array($type, ['pay_jail_fee', 'use_jail_card'], true) && ! $player->is_in_jail) {
            throw ValidationException::withMessages(['jail' => 'Kamu tidak sedang di penjara.']);
        }

        if ($type === 'use_jail_card' && $player->jail_free_cards <= 0) {
            throw ValidationException::withMessages(['jail_free_cards' => 'Kamu belum punya kartu bebas penjara.']);
        }
    }

    private function ensureNoDuplicatePendingRequest(Game $game, Player $player, string $type, ?GameProperty $property, ?Player $target, array $payload): void
    {
        $duplicate = TransactionRequest::query()
            ->where('game_id', $game->id)
            ->where('player_id', $player->id)
            ->where('type', $type)
            ->where('status', 'pending')
            ->when($property, fn ($query) => $query->where('game_property_id', $property->id))
            ->when($target, fn ($query) => $query->where('target_player_id', $target->id))
            ->when(isset($payload['amount']), fn ($query) => $query->where('amount', (int) $payload['amount']))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages(['request' => 'Permintaan yang sama masih menunggu persetujuan Bank.']);
        }
    }

    private function propertyPayload(GameProperty $gameProperty, array $completeGroups = []): array
    {
        $hasCompleteGroup = $gameProperty->owner_id
            && in_array($gameProperty->property->group_code, $completeGroups[$gameProperty->owner_id] ?? [], true);
        $currentRent = $this->currentRent($gameProperty, $hasCompleteGroup);

        return [
            'id' => $gameProperty->id,
            'property_id' => $gameProperty->property_id,
            'name' => $gameProperty->property->name,
            'group_code' => $gameProperty->property->group_code,
            'group_name' => $gameProperty->property->group_name,
            'property_kind' => $gameProperty->property->property_kind,
            'price' => $gameProperty->property->price,
            'house_price' => $gameProperty->property->house_price,
            'hotel_price' => $gameProperty->property->hotel_price,
            'rent' => $gameProperty->property->rent,
            'rent_1_house' => $gameProperty->property->rent_1_house,
            'rent_2_houses' => $gameProperty->property->rent_2_houses,
            'rent_3_houses' => $gameProperty->property->rent_3_houses,
            'rent_4_houses' => $gameProperty->property->rent_4_houses,
            'rent_hotel' => $gameProperty->property->rent_hotel,
            'mortgage_value' => $gameProperty->property->mortgage_value,
            'current_rent' => $currentRent,
            'has_complete_group' => $hasCompleteGroup,
            'color' => $gameProperty->property->color,
            'owner_id' => $gameProperty->owner_id,
            'owner_name' => $gameProperty->owner?->name,
            'house_count' => $gameProperty->house_count,
            'has_hotel' => $gameProperty->has_hotel,
            'is_mortgaged' => $gameProperty->is_mortgaged,
        ];
    }

    private function completeGroups(Game $game): array
    {
        $groupSizes = Property::query()
            ->where('property_kind', 'land')
            ->whereNotNull('group_code')
            ->selectRaw('group_code, count(*) as total')
            ->groupBy('group_code')
            ->pluck('total', 'group_code');

        return $game->gameProperties
            ->filter(fn (GameProperty $gameProperty) => $gameProperty->owner_id && $gameProperty->property->property_kind === 'land')
            ->groupBy('owner_id')
            ->map(function (EloquentCollection $owned) use ($groupSizes) {
                return $owned
                    ->groupBy('property.group_code')
                    ->filter(fn (EloquentCollection $items, string $groupCode) => $items->count() === (int) ($groupSizes[$groupCode] ?? 0))
                    ->keys()
                    ->values()
                    ->all();
            })
            ->all();
    }

    private function currentRent(GameProperty $gameProperty, bool $hasCompleteGroup): int
    {
        if ($gameProperty->property->property_kind === 'utility') {
            return 7500;
        }

        if ($gameProperty->property->property_kind === 'transport') {
            $ownedCount = $gameProperty->owner_id
                ? GameProperty::query()
                    ->where('game_id', $gameProperty->game_id)
                    ->where('owner_id', $gameProperty->owner_id)
                    ->whereHas('property', fn ($query) => $query->where('property_kind', 'transport'))
                    ->count()
                : 1;

            return match ($ownedCount) {
                2 => $gameProperty->property->rent_1_house,
                3 => $gameProperty->property->rent_2_houses,
                4 => $gameProperty->property->rent_3_houses,
                default => $gameProperty->property->rent,
            };
        }

        if ($gameProperty->has_hotel) {
            return $gameProperty->property->rent_hotel;
        }

        $rent = match ($gameProperty->house_count) {
            1 => $gameProperty->property->rent_1_house,
            2 => $gameProperty->property->rent_2_houses,
            3 => $gameProperty->property->rent_3_houses,
            4 => $gameProperty->property->rent_4_houses,
            default => $gameProperty->property->rent,
        };

        return $hasCompleteGroup ? $rent * 2 : $rent;
    }

    private function transactionPayload(Transaction $transaction): array
    {
        return [
            'id' => $transaction->id,
            'type' => $transaction->type,
            'amount' => $transaction->amount,
            'description' => $transaction->description,
            'from_player_id' => $transaction->from_player_id,
            'to_player_id' => $transaction->to_player_id,
            'from_player' => $transaction->fromPlayer?->name,
            'to_player' => $transaction->toPlayer?->name,
            'property' => $transaction->gameProperty?->property?->name,
            'meta' => $transaction->meta,
            'created_at' => $transaction->created_at?->toIso8601String(),
            'created_at_label' => $transaction->created_at?->format('H:i:s'),
        ];
    }

    private function turnPayload(Game $game): array
    {
        $order = $game->turn_order ?: ($game->first_player_id ? $this->buildTurnOrder($game, $game->first_player_id) : []);
        $playersById = $game->players->keyBy('id');

        return [
            'current_player_id' => $game->current_turn_player_id,
            'current_player' => $game->currentTurnPlayer ? $this->playerBrief($game->currentTurnPlayer) : null,
            'turn_number' => $game->turn_number,
            'order' => collect($order)
                ->map(fn ($playerId) => $playersById->get($playerId))
                ->filter()
                ->map(fn (Player $player) => [
                    'id' => $player->id,
                    'name' => $player->name,
                    'avatar_color' => $player->avatar_color,
                    'is_bankrupt' => $player->is_bankrupt,
                    'is_in_jail' => $player->is_in_jail,
                    'pending_jail_release' => $player->pending_jail_release,
                    'board_position' => $player->board_position,
                    'current_space' => $this->boardSpace($player->board_position),
                ])
                ->values()
                ->all(),
            'last_roll' => $game->diceRolls->first() ? $this->diceRollPayload($game->diceRolls->first()) : null,
            'recent_rolls' => $game->diceRolls
                ->take(8)
                ->map(fn (DiceRoll $roll) => $this->diceRollPayload($roll))
                ->values()
                ->all(),
        ];
    }

    private function diceRollPayload(DiceRoll $roll): array
    {
        return [
            'id' => $roll->id,
            'player_id' => $roll->player_id,
            'player_name' => $roll->player?->name,
            'player_color' => $roll->player?->avatar_color,
            'dice_one' => $roll->dice_one,
            'dice_two' => $roll->dice_two,
            'total' => $roll->total,
            'is_double' => $roll->is_double,
            'turn_number' => $roll->turn_number,
            'result' => $roll->result,
            'meta' => $roll->meta,
            'created_at_label' => $roll->created_at?->format('H:i:s'),
        ];
    }

    private function usedCardKeys(Game $game): array
    {
        $lastRefreshId = $game->transactions
            ->where('type', 'card_refresh')
            ->max('id') ?? 0;

        return $game->transactions
            ->filter(fn (Transaction $transaction) => $transaction->id > $lastRefreshId && data_get($transaction->meta, 'card_key'))
            ->pluck('meta.card_key')
            ->unique()
            ->values()
            ->all();
    }

    private function playerBrief(Player $player): array
    {
        return [
            'id' => $player->id,
            'name' => $player->name,
            'rfid_uid' => $player->rfid_uid,
            'avatar_color' => $player->avatar_color,
            'balance' => $player->balance,
            'cash_balance' => $player->cash_balance,
            'is_bankrupt' => $player->is_bankrupt,
        ];
    }

    private function transactionChart(EloquentCollection $transactions): array
    {
        $moneyTransactions = $transactions
            ->reject(fn (Transaction $transaction) => in_array($transaction->type, ['rfid_scan', 'game_started', 'game_paused', 'game_resumed', 'game_reset'], true));
        $byType = $moneyTransactions
            ->groupBy('type')
            ->map(fn (EloquentCollection $items, string $type) => [
                'label' => Str::headline($type),
                'count' => $items->count(),
                'amount' => $items->sum('amount'),
            ])
            ->sortByDesc('amount')
            ->take(8)
            ->values();

        if ($byType->isEmpty()) {
            return [
                'labels' => ['Belum ada'],
                'data' => [0],
                'counts' => [0],
                'total_amount' => 0,
            ];
        }

        return [
            'labels' => $byType->pluck('label')->all(),
            'data' => $byType->pluck('amount')->all(),
            'counts' => $byType->pluck('count')->all(),
            'total_amount' => $moneyTransactions->sum('amount'),
        ];
    }

    private function wealthHistoryChart(Game $game, \Illuminate\Support\Collection $playerRows): array
    {
        $startingCash = (int) Setting::getValue('starting_cash', 0);
        $initialAsset = $game->starting_balance + $startingCash;

        return $playerRows
            ->reject(fn ($player) => $player['is_bankrupt'])
            ->map(fn ($player) => [
                'label' => $player['name'],
                'data' => [$initialAsset, $player['total_asset']],
                'borderColor' => $player['avatar_color'],
                'backgroundColor' => $player['avatar_color'] . '33',
                'tension' => 0.35,
                'fill' => false,
            ])
            ->values()
            ->all();
    }

    private function ownershipSummary(\Illuminate\Support\Collection $playerRows): array
    {
        return $playerRows
            ->map(fn ($player) => [
                'name' => $player['name'],
                'color' => $player['avatar_color'],
                'properties' => $player['property_count'],
                'houses' => $player['house_count'],
                'hotels' => $player['hotel_count'],
                'value' => $player['property_value'] + $player['house_value'] + $player['hotel_value'],
            ])
            ->sortByDesc('value')
            ->values()
            ->all();
    }

    private function gameListItem(Game $game): array
    {
        $summary = $game->finish_summary;
        $players = $game->players->pluck('name')->all();
        $totalAssets = collect(data_get($summary, 'players', []))
            ->reject(fn ($player) => data_get($player, 'is_bankrupt'))
            ->sum('total_asset');

        return [
            'id' => $game->id,
            'code' => $game->code,
            'status' => $game->status,
            'date' => ($game->ended_at ?? $game->started_at ?? $game->created_at)?->format('d M Y H:i'),
            'players' => $players,
            'winner' => $game->winner?->name,
            'total_assets' => $totalAssets,
            'duration_seconds' => $this->durationSeconds($game),
            'duration_minutes' => $game->duration_minutes,
            'remaining_seconds' => $this->remainingSeconds($game),
            'transaction_count' => $game->transactions->count(),
        ];
    }

    private function durationSeconds(Game $game): int
    {
        $start = $game->started_at ?? $game->created_at ?? Carbon::now();
        $end = $game->ended_at ?? Carbon::now();

        return $start->diffInSeconds($end);
    }

    private function endsAt(Game $game): ?Carbon
    {
        if (! $game->duration_minutes || ! $game->started_at) {
            return null;
        }

        return $game->started_at->copy()->addMinutes($game->duration_minutes);
    }

    private function remainingSeconds(Game $game): ?int
    {
        $endsAt = $this->endsAt($game);

        if (! $endsAt) {
            return null;
        }

        if ($game->status === 'finished') {
            return 0;
        }

        return (int) max(0, now()->diffInSeconds($endsAt, false));
    }
}
