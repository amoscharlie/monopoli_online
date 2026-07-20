<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\GameCard;
use App\Models\GameCardDraw;
use App\Models\Property;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonopolyBankTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_new_game_without_login(): void
    {
        $this->seed();

        $response = $this->postJson('/api/games', [
            'starting_balance' => 15000,
            'starting_cash' => 500,
            'duration_minutes' => 60,
            'players' => [
                ['name' => 'Amos', 'rfid_uid' => '04 A1'],
                ['name' => 'Budi', 'rfid_uid' => '04 B2'],
            ],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('state.game.player_count', 2)
            ->assertJsonPath('state.players.0.balance', 15000)
            ->assertJsonPath('state.players.0.cash_balance', 500)
            ->assertJsonPath('state.game.duration_minutes', 60);

        $this->assertDatabaseHas('players', ['name' => 'Amos', 'rfid_uid' => '04A1']);
        $this->assertDatabaseHas('players', ['name' => 'Amos', 'cash_balance' => 500]);
        $this->assertDatabaseCount('game_properties', Property::query()->count());
    }

    public function test_it_creates_game_with_up_to_eight_players_and_unlimited_time(): void
    {
        $this->seed();

        $players = collect(range(1, 8))
            ->map(fn (int $index) => [
                'name' => "Pemain {$index}",
                'rfid_uid' => "rfid-{$index}",
            ])
            ->all();

        $this->postJson('/api/games', [
            'starting_balance' => 15000,
            'starting_cash' => 0,
            'duration_minutes' => null,
            'players' => $players,
        ])
            ->assertCreated()
            ->assertJsonPath('state.game.player_count', 8)
            ->assertJsonPath('state.game.duration_minutes', null)
            ->assertJsonPath('state.game.remaining_seconds', null);
    }

    public function test_it_can_create_players_without_rfid_cards(): void
    {
        $this->seed();

        $this->postJson('/api/games', [
            'starting_balance' => 150000,
            'starting_cash' => 0,
            'duration_minutes' => null,
            'players' => [
                ['name' => 'Amos', 'rfid_uid' => ''],
                ['name' => 'Budi', 'rfid_uid' => null],
            ],
        ])
            ->assertCreated()
            ->assertJsonPath('state.players.0.name', 'Amos')
            ->assertJsonPath('state.players.0.rfid_uid', null);

        $this->assertDatabaseHas('players', ['name' => 'Amos', 'rfid_uid' => null]);
        $this->assertDatabaseCount('rfid_cards', 0);
    }

    public function test_deposit_and_withdraw_move_money_between_bank_balance_and_cash(): void
    {
        $this->seed();
        $gameId = $this->createGame(1000, 300);
        $game = Game::query()->with('players')->findOrFail($gameId);
        $player = $game->players->first();

        $this->postJson("/api/games/{$gameId}/transactions/withdraw", [
            'player_id' => $player->id,
            'amount' => 200,
            'reason' => 'Ambil uang fisik',
        ])->assertOk();

        $this->assertDatabaseHas('players', [
            'id' => $player->id,
            'balance' => 800,
            'cash_balance' => 500,
        ]);

        $this->postJson("/api/games/{$gameId}/transactions/deposit", [
            'player_id' => $player->id,
            'amount' => 100,
            'reason' => 'Setor cash',
        ])->assertOk();

        $this->assertDatabaseHas('players', [
            'id' => $player->id,
            'balance' => 900,
            'cash_balance' => 400,
        ]);
    }

    public function test_rfid_scan_highlights_the_matching_player(): void
    {
        $this->seed();
        $gameId = $this->createGame();

        $response = $this->postJson('/api/rfid', [
            'UID' => 'rfid-amos',
            'game_id' => $gameId,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('matched', true)
            ->assertJsonPath('player.name', 'Amos');

        $this->assertSame(
            Game::query()->find($gameId)->players()->where('name', 'Amos')->value('id'),
            Game::query()->find($gameId)->last_scanned_player_id
        );
    }

    public function test_transfer_cannot_make_balance_negative(): void
    {
        $this->seed();
        $gameId = $this->createGame(1000);
        $game = Game::query()->with('players')->findOrFail($gameId);
        $amos = $game->players->firstWhere('name', 'Amos');
        $budi = $game->players->firstWhere('name', 'Budi');

        $this->postJson("/api/games/{$gameId}/transactions/transfer", [
            'from_player_id' => $amos->id,
            'to_player_id' => $budi->id,
            'amount' => 1500,
        ])->assertUnprocessable();

        $this->assertDatabaseHas('players', ['id' => $amos->id, 'balance' => 1000]);
        $this->assertDatabaseHas('players', ['id' => $budi->id, 'balance' => 1000]);
    }

    public function test_transfer_can_use_physical_cash_between_players(): void
    {
        $this->seed();
        $gameId = $this->createGame(1000, 300);
        $game = Game::query()->with('players')->findOrFail($gameId);
        $amos = $game->players->firstWhere('name', 'Amos');
        $budi = $game->players->firstWhere('name', 'Budi');

        $this->postJson("/api/games/{$gameId}/transactions/transfer", [
            'from_player_id' => $amos->id,
            'to_player_id' => $budi->id,
            'amount' => 200,
            'source' => 'cash',
        ])->assertOk();

        $this->assertDatabaseHas('players', [
            'id' => $amos->id,
            'balance' => 1000,
            'cash_balance' => 100,
        ]);
        $this->assertDatabaseHas('players', [
            'id' => $budi->id,
            'balance' => 1000,
            'cash_balance' => 500,
        ]);

        $this->postJson("/api/games/{$gameId}/transactions/transfer", [
            'from_player_id' => $amos->id,
            'to_player_id' => $budi->id,
            'amount' => 200,
            'source' => 'cash',
        ])->assertUnprocessable();
    }

    public function test_it_deletes_continue_game_sessions(): void
    {
        $this->seed();
        $gameId = $this->createGame();

        $this->deleteJson("/api/games/{$gameId}")
            ->assertOk()
            ->assertJsonPath('active_games', []);

        $this->assertDatabaseMissing('games', ['id' => $gameId]);
        $this->assertDatabaseMissing('players', ['game_id' => $gameId]);
        $this->assertDatabaseMissing('rfid_cards', ['game_id' => $gameId]);
    }

    public function test_it_deletes_finished_history_games(): void
    {
        $this->seed();
        $gameId = $this->createGame();

        $this->postJson("/api/games/{$gameId}/finish")->assertOk();
        $this->deleteJson("/api/games/{$gameId}/history")
            ->assertOk()
            ->assertJsonPath('history_games', []);

        $this->assertDatabaseMissing('games', ['id' => $gameId]);
    }

    public function test_first_player_can_be_selected_once_for_a_game(): void
    {
        $this->seed();
        $gameId = $this->createGame();
        $game = Game::query()->with('players')->findOrFail($gameId);
        $amos = $game->players->firstWhere('name', 'Amos');
        $budi = $game->players->firstWhere('name', 'Budi');

        $this->getJson("/api/games/{$gameId}")
            ->assertOk()
            ->assertJsonPath('state.game.needs_first_player_spin', true);

        $this->postJson("/api/games/{$gameId}/first-player", [
            'player_id' => $amos->id,
        ])
            ->assertOk()
            ->assertJsonPath('state.game.needs_first_player_spin', false)
            ->assertJsonPath('state.game.first_player.name', 'Amos');

        $this->postJson("/api/games/{$gameId}/first-player", [
            'player_id' => $budi->id,
        ])
            ->assertOk()
            ->assertJsonPath('state.game.first_player.name', 'Amos');
    }

    public function test_player_portal_can_roll_dice_only_on_their_turn(): void
    {
        $this->seed();
        $gameId = $this->createGame();
        $game = Game::query()->with('players', 'playerAccessTokens')->findOrFail($gameId);
        $amos = $game->players->firstWhere('name', 'Amos');
        $budi = $game->players->firstWhere('name', 'Budi');
        $amosToken = $game->playerAccessTokens->firstWhere('player_id', $amos->id);
        $budiToken = $game->playerAccessTokens->firstWhere('player_id', $budi->id);

        $this->postJson("/api/games/{$gameId}/first-player", [
            'player_id' => $amos->id,
        ])->assertOk();

        $this->postJson("/api/player/{$budiToken->token}/roll-dice")
            ->assertUnprocessable();

        $this->postJson("/api/player/{$amosToken->token}/roll-dice")
            ->assertOk()
            ->assertJsonPath('message', 'Dadu berhasil dikocok.');

        $this->assertDatabaseHas('dice_rolls', [
            'game_id' => $gameId,
            'player_id' => $amos->id,
        ]);
    }

    public function test_card_can_collect_money_from_every_other_player(): void
    {
        $this->seed();
        $gameId = $this->postJson('/api/games', [
            'starting_balance' => 50000,
            'starting_cash' => 0,
            'duration_minutes' => 60,
            'players' => [
                ['name' => 'Amos', 'rfid_uid' => 'rfid-amos'],
                ['name' => 'Budi', 'rfid_uid' => 'rfid-budi'],
                ['name' => 'Rina', 'rfid_uid' => 'rfid-rina'],
            ],
        ])->json('state.game.id');

        $game = Game::query()->with('players')->findOrFail($gameId);
        $amos = $game->players->firstWhere('name', 'Amos');
        $budi = $game->players->firstWhere('name', 'Budi');
        $rina = $game->players->firstWhere('name', 'Rina');

        $this->postJson("/api/games/{$gameId}/transactions/collect-from-players", [
            'player_id' => $amos->id,
            'amount' => 10000,
            'reason' => 'Dana Umum: Hari ulang tahun',
        ])->assertOk();

        $this->assertDatabaseHas('players', ['id' => $amos->id, 'balance' => 70000]);
        $this->assertDatabaseHas('players', ['id' => $budi->id, 'balance' => 40000]);
        $this->assertDatabaseHas('players', ['id' => $rina->id, 'balance' => 40000]);
    }

    public function test_player_can_go_bankrupt_and_release_assets(): void
    {
        $this->seed();
        $gameId = $this->createGame(500000);
        $game = Game::query()->with('players', 'gameProperties.property')->findOrFail($gameId);
        $player = $game->players->first();
        $gameProperty = $game->gameProperties->first();
        $property = $gameProperty->property;

        $this->postJson("/api/games/{$gameId}/transactions/buy-property", [
            'player_id' => $player->id,
            'game_property_id' => $gameProperty->id,
        ])->assertOk();
        $this->postJson("/api/games/{$gameId}/transactions/add-house", [
            'player_id' => $player->id,
            'game_property_id' => $gameProperty->id,
        ])->assertOk();

        $preview = $this->postJson("/api/games/{$gameId}/transactions/bankruptcy-preview", [
            'player_id' => $player->id,
        ])->assertOk();

        $expectedSale = intdiv($property->price, 2) + intdiv($property->house_price, 2);
        $this->assertSame($expectedSale, $preview->json('summary.sale_total'));

        $this->postJson("/api/games/{$gameId}/transactions/bankrupt", [
            'player_id' => $player->id,
        ])
            ->assertOk()
            ->assertJsonFragment([
                'id' => $player->id,
                'is_bankrupt' => true,
                'total_asset' => 0,
            ]);

        $this->assertDatabaseHas('game_properties', [
            'id' => $gameProperty->id,
            'owner_id' => null,
            'house_count' => 0,
        ]);
        $this->postJson("/api/games/{$gameId}/transactions/bank-to-player", [
            'player_id' => $player->id,
            'amount' => 1000,
        ])->assertUnprocessable();
    }

    public function test_card_actions_are_locked_until_refreshed(): void
    {
        $this->seed();
        $gameId = $this->createGame(50000);
        $game = Game::query()->with('players')->findOrFail($gameId);
        $amos = $game->players->firstWhere('name', 'Amos');

        $payload = [
            'player_id' => $amos->id,
            'amount' => 5000,
            'reason' => 'Dana Umum: Dapat komisi',
            'card_key' => 'dana_komisi',
        ];

        $this->postJson("/api/games/{$gameId}/transactions/bank-to-player", $payload)
            ->assertOk()
            ->assertJsonPath('state.cards.used_keys.0', 'dana_komisi');

        $this->postJson("/api/games/{$gameId}/transactions/bank-to-player", $payload)
            ->assertUnprocessable();

        $this->postJson("/api/games/{$gameId}/cards/refresh")
            ->assertOk()
            ->assertJsonPath('state.cards.used_keys', []);

        $this->postJson("/api/games/{$gameId}/transactions/bank-to-player", $payload)
            ->assertOk();
    }

    public function test_houses_and_hotels_follow_monopoly_sale_rules(): void
    {
        $this->seed();
        $gameId = $this->createGame(500000);
        $game = Game::query()->with('players', 'gameProperties.property')->findOrFail($gameId);
        $player = $game->players->first();
        $gameProperty = $game->gameProperties->first();

        $this->postJson("/api/games/{$gameId}/transactions/buy-property", [
            'player_id' => $player->id,
            'game_property_id' => $gameProperty->id,
        ])->assertOk();

        // Hotel boleh dibeli langsung jika properti belum punya rumah.
        $this->postJson("/api/games/{$gameId}/transactions/add-hotel", [
            'player_id' => $player->id,
            'game_property_id' => $gameProperty->id,
        ])->assertOk();

        $this->assertDatabaseHas('game_properties', [
            'id' => $gameProperty->id,
            'house_count' => 0,
            'has_hotel' => true,
        ]);

        // Kalau sudah punya hotel, tidak boleh tambah rumah.
        $this->postJson("/api/games/{$gameId}/transactions/add-house", [
            'player_id' => $player->id,
            'game_property_id' => $gameProperty->id,
        ])->assertUnprocessable();

        // Jual hotel dulu, baru bisa pilih jalur rumah lagi.
        $this->postJson("/api/games/{$gameId}/transactions/sell-hotel", [
            'player_id' => $player->id,
            'game_property_id' => $gameProperty->id,
        ])->assertOk();

        // Rumah harus dibeli berurutan sampai maksimal 4.
        $this->postJson("/api/games/{$gameId}/transactions/sell-house", [
            'player_id' => $player->id,
            'game_property_id' => $gameProperty->id,
        ])->assertUnprocessable();

        $this->postJson("/api/games/{$gameId}/transactions/add-house", [
            'player_id' => $player->id,
            'game_property_id' => $gameProperty->id,
        ])->assertOk();

        // Selama masih ada rumah, tidak boleh langsung beli hotel.
        $this->postJson("/api/games/{$gameId}/transactions/add-hotel", [
            'player_id' => $player->id,
            'game_property_id' => $gameProperty->id,
        ])->assertUnprocessable();

        $this->postJson("/api/games/{$gameId}/transactions/sell-house", [
            'player_id' => $player->id,
            'game_property_id' => $gameProperty->id,
        ])->assertOk();

        // Setelah rumah habis dijual, hotel bisa dibeli lagi.
        $this->postJson("/api/games/{$gameId}/transactions/add-hotel", [
            'player_id' => $player->id,
            'game_property_id' => $gameProperty->id,
        ])->assertOk();

        $this->postJson("/api/games/{$gameId}/transactions/sell-hotel", [
            'player_id' => $player->id,
            'game_property_id' => $gameProperty->id,
        ])->assertOk();

        for ($i = 0; $i < 4; $i++) {
            $this->postJson("/api/games/{$gameId}/transactions/add-house", [
                'player_id' => $player->id,
                'game_property_id' => $gameProperty->id,
            ])->assertOk();
        }

        $this->postJson("/api/games/{$gameId}/transactions/add-house", [
            'player_id' => $player->id,
            'game_property_id' => $gameProperty->id,
        ])->assertUnprocessable();
    }

    public function test_transport_and_utility_cannot_buy_house_or_hotel(): void
    {
        $this->seed();
        $gameId = $this->createGame(500000);
        $game = Game::query()->with('players', 'gameProperties.property')->findOrFail($gameId);
        $player = $game->players->first();
        $utility = $game->gameProperties->first(fn ($item) => $item->property->property_kind === 'utility');
        $transport = $game->gameProperties->first(fn ($item) => $item->property->property_kind === 'transport');

        $this->postJson("/api/games/{$gameId}/transactions/buy-property", [
            'player_id' => $player->id,
            'game_property_id' => $utility->id,
        ])->assertOk();
        $this->postJson("/api/games/{$gameId}/transactions/buy-property", [
            'player_id' => $player->id,
            'game_property_id' => $transport->id,
        ])->assertOk();

        $this->postJson("/api/games/{$gameId}/transactions/add-house", [
            'player_id' => $player->id,
            'game_property_id' => $utility->id,
        ])->assertUnprocessable();
        $this->postJson("/api/games/{$gameId}/transactions/add-hotel", [
            'player_id' => $player->id,
            'game_property_id' => $utility->id,
        ])->assertUnprocessable();
        $this->postJson("/api/games/{$gameId}/transactions/add-house", [
            'player_id' => $player->id,
            'game_property_id' => $transport->id,
        ])->assertUnprocessable();
        $this->postJson("/api/games/{$gameId}/transactions/add-hotel", [
            'player_id' => $player->id,
            'game_property_id' => $transport->id,
        ])->assertUnprocessable();
    }

    public function test_player_portal_can_request_property_purchase_for_admin_approval(): void
    {
        $this->seed();
        $gameId = $this->createGame(50000);
        $game = Game::query()->with('players', 'gameProperties.property', 'playerAccessTokens')->findOrFail($gameId);
        $amos = $game->players->firstWhere('name', 'Amos');
        $token = $game->playerAccessTokens->firstWhere('player_id', $amos->id);
        $gameProperty = $game->gameProperties->first();
        $price = $gameProperty->property->price;

        $this->get("/player/{$token->token}")
            ->assertOk()
            ->assertSee('HP Pemain');

        $this->getJson("/api/player/{$token->token}/state")
            ->assertOk()
            ->assertJsonPath('player.name', 'Amos');

        $request = $this->postJson("/api/player/{$token->token}/requests", [
            'type' => 'buy_property',
            'game_property_id' => $gameProperty->id,
        ])
            ->assertCreated()
            ->json('request_id');

        $this->getJson("/api/games/{$gameId}")
            ->assertOk()
            ->assertJsonPath('state.player_portal.pending_requests.0.id', $request);

        $this->postJson("/api/games/{$gameId}/requests/{$request}/approve")
            ->assertOk()
            ->assertJsonPath('state.player_portal.pending_requests', []);

        $this->assertDatabaseHas('game_properties', [
            'id' => $gameProperty->id,
            'owner_id' => $amos->id,
        ]);
        $this->assertDatabaseHas('transactions', [
            'game_id' => $gameId,
            'type' => 'buy_property',
            'from_player_id' => $amos->id,
            'game_property_id' => $gameProperty->id,
        ]);
    }

    public function test_player_portal_can_submit_bulk_sell_assets_request_and_bank_approves_once(): void
    {
        $this->seed();
        $gameId = $this->createGame(200000);
        $game = Game::query()->with('players', 'gameProperties.property', 'playerAccessTokens')->findOrFail($gameId);
        $amos = $game->players->firstWhere('name', 'Amos');
        $token = $game->playerAccessTokens->firstWhere('player_id', $amos->id);
        $property = $game->gameProperties->first();

        $this->postJson("/api/games/{$gameId}/transactions/buy-property", [
            'player_id' => $amos->id,
            'game_property_id' => $property->id,
        ])->assertOk();

        $this->postJson("/api/games/{$gameId}/transactions/add-house", [
            'player_id' => $amos->id,
            'game_property_id' => $property->id,
        ])->assertOk();

        $requestId = $this->postJson("/api/player/{$token->token}/requests", [
            'type' => 'bulk_sell_assets',
            'bulk_total' => intdiv($property->property->house_price, 2) + intdiv($property->property->price, 2),
            'liquidation_plan' => [
                ['type' => 'sell_house', 'game_property_id' => $property->id, 'quantity' => 1],
                ['type' => 'sell_property', 'game_property_id' => $property->id, 'quantity' => 1],
            ],
        ])
            ->assertCreated()
            ->json('request_id');

        $this->postJson("/api/games/{$gameId}/requests/{$requestId}/approve")
            ->assertOk();

        $this->assertDatabaseHas('transaction_requests', [
            'id' => $requestId,
            'status' => 'approved',
            'amount' => intdiv($property->property->house_price, 2) + intdiv($property->property->price, 2),
        ]);
        $this->assertDatabaseHas('transactions', [
            'game_id' => $gameId,
            'type' => 'sell_house',
            'to_player_id' => $amos->id,
            'game_property_id' => $property->id,
        ]);
        $this->assertDatabaseHas('transactions', [
            'game_id' => $gameId,
            'type' => 'sell_property',
            'to_player_id' => $amos->id,
            'game_property_id' => $property->id,
        ]);
        $this->assertDatabaseHas('game_properties', [
            'id' => $property->id,
            'owner_id' => null,
            'house_count' => 0,
            'has_hotel' => false,
        ]);
    }

    public function test_player_portal_rejects_rent_request_to_owned_property(): void
    {
        $this->seed();
        $gameId = $this->createGame(50000);
        $game = Game::query()->with('players', 'gameProperties', 'playerAccessTokens')->findOrFail($gameId);
        $amos = $game->players->firstWhere('name', 'Amos');
        $token = $game->playerAccessTokens->firstWhere('player_id', $amos->id);
        $gameProperty = $game->gameProperties->first();

        $this->postJson("/api/games/{$gameId}/transactions/buy-property", [
            'player_id' => $amos->id,
            'game_property_id' => $gameProperty->id,
        ])->assertOk();

        $this->postJson("/api/player/{$token->token}/requests", [
            'type' => 'pay_rent',
            'game_property_id' => $gameProperty->id,
            'amount' => 1000,
            'owner_id' => $amos->id,
        ])->assertUnprocessable();

        $this->assertDatabaseMissing('transaction_requests', [
            'game_id' => $gameId,
            'type' => 'pay_rent',
            'player_id' => $amos->id,
        ]);
    }

    public function test_player_portal_pays_rent_directly_without_admin_approval(): void
    {
        $this->seed();
        $gameId = $this->createGame(50000);
        $game = Game::query()->with('players', 'gameProperties.property', 'playerAccessTokens')->findOrFail($gameId);
        $amos = $game->players->firstWhere('name', 'Amos');
        $budi = $game->players->firstWhere('name', 'Budi');
        $token = $game->playerAccessTokens->firstWhere('player_id', $amos->id);
        $gameProperty = $game->gameProperties->first();
        $rent = $gameProperty->property->rent;

        $this->postJson("/api/games/{$gameId}/transactions/buy-property", [
            'player_id' => $budi->id,
            'game_property_id' => $gameProperty->id,
        ])->assertOk();

        $this->postJson("/api/player/{$token->token}/pay-rent", [
            'game_property_id' => $gameProperty->id,
            'source' => 'bank',
        ])->assertOk();

        $this->assertDatabaseHas('transactions', [
            'game_id' => $gameId,
            'type' => 'pay_rent',
            'from_player_id' => $amos->id,
            'to_player_id' => $budi->id,
            'amount' => $rent,
        ]);
        $this->assertDatabaseCount('transaction_requests', 0);
    }

    public function test_player_portal_can_skip_stale_receive_card_action(): void
    {
        $this->seed();
        $gameId = $this->createGame(50000);
        $game = Game::query()->with('players', 'playerAccessTokens')->findOrFail($gameId);
        $amos = $game->players->firstWhere('name', 'Amos');
        $token = $game->playerAccessTokens->firstWhere('player_id', $amos->id);

        $amos->update([
            'pending_space_action' => [
                'action' => 'card_receive',
                'label' => 'Dana Umum: Dapat Sisa Uang Pajak Jalan',
                'message' => 'Dapat sisa uang pajak jalan 5000.',
                'amount' => 5000,
                'requires_resolution' => false,
                'auto_resolved' => true,
            ],
        ]);

        $this->postJson("/api/player/{$token->token}/space-action", [
            'decision' => 'skip',
            'source' => 'bank',
        ])->assertOk();

        $this->assertDatabaseHas('players', [
            'id' => $amos->id,
            'pending_space_action' => null,
        ]);
    }

    public function test_repair_card_without_assets_is_auto_resolved(): void
    {
        $this->seed();
        $gameId = $this->createGame(50000);
        $game = Game::query()->with('players', 'playerAccessTokens')->findOrFail($gameId);
        $amos = $game->players->firstWhere('name', 'Amos');
        $token = $game->playerAccessTokens->firstWhere('player_id', $amos->id);
        $card = GameCard::query()->where('effect_type', 'repair_assets')->firstOrFail();

        $draw = GameCardDraw::query()->create([
            'game_id' => $gameId,
            'game_card_id' => $card->id,
            'player_id' => $amos->id,
            'deck' => $card->deck,
            'status' => 'drawn',
            'snapshot' => [
                'key' => $card->key,
                'title' => $card->title,
                'effect_type' => $card->effect_type,
            ],
        ]);

        $amos->update([
            'pending_space_action' => [
                'action' => 'card_repair_assets',
                'card_draw_id' => $draw->id,
                'label' => "{$card->deck}: {$card->title}",
                'message' => $card->description,
                'amount' => 0,
                'requires_resolution' => true,
            ],
        ]);

        $this->postJson("/api/player/{$token->token}/space-action", [
            'decision' => 'pay',
            'source' => 'bank',
        ])->assertOk();

        $draw->refresh();
        $amos->refresh();

        $this->assertSame('resolved', $draw->status);
        $this->assertNull($amos->pending_space_action);
        $this->assertDatabaseHas('transactions', [
            'game_id' => $gameId,
            'type' => 'card_no_payment',
            'to_player_id' => $amos->id,
        ]);
    }

    public function test_move_card_creates_follow_up_space_action(): void
    {
        $this->seed();
        $forceCardDraw = function (int $gameId, int $playerId, string $targetKey): void {
            $targetCard = GameCard::query()->where('key', $targetKey)->firstOrFail();
            $otherCards = GameCard::query()
                ->where('deck', $targetCard->deck)
                ->where('key', '!=', $targetKey)
                ->get();

            foreach ($otherCards as $otherCard) {
                GameCardDraw::query()->create([
                    'game_id' => $gameId,
                    'game_card_id' => $otherCard->id,
                    'player_id' => $playerId,
                    'deck' => $otherCard->deck,
                    'status' => 'resolved',
                    'snapshot' => [
                        'key' => $otherCard->key,
                        'title' => $otherCard->title,
                    ],
                    'resolved_at' => now(),
                ]);
            }
        };

        $gameIdRent = $this->createGame(50000);
        $gameRent = Game::query()->with('players', 'gameProperties.property')->findOrFail($gameIdRent);
        $amosRent = $gameRent->players->firstWhere('name', 'Amos');
        $budiRent = $gameRent->players->firstWhere('name', 'Budi');
        $mesir = $gameRent->gameProperties->first(fn ($item) => str_contains(strtolower($item->property->name), 'mesir'));

        $this->postJson("/api/games/{$gameIdRent}/transactions/buy-property", [
            'player_id' => $budiRent->id,
            'game_property_id' => $mesir->id,
        ])->assertOk();

        $forceCardDraw($gameIdRent, $amosRent->id, 'kesempatan_maju_mesir');
        $this->postJson("/api/games/{$gameIdRent}/cards/draw", [
            'player_id' => $amosRent->id,
            'deck' => 'Kesempatan',
        ])->assertOk();

        $this->assertDatabaseHas('players', [
            'id' => $amosRent->id,
            'pending_space_action->action' => 'pay_rent',
            'pending_space_action->game_property_id' => $mesir->id,
        ]);

        $gameIdBuy = $this->createGame(50000);
        $gameBuy = Game::query()->with('players', 'gameProperties.property')->findOrFail($gameIdBuy);
        $amosBuy = $gameBuy->players->firstWhere('name', 'Amos');
        $indonesia = $gameBuy->gameProperties->first(fn ($item) => str_contains(strtolower($item->property->name), 'indonesia'));

        $forceCardDraw($gameIdBuy, $amosBuy->id, 'kesempatan_kembali_indonesia');
        $this->postJson("/api/games/{$gameIdBuy}/cards/draw", [
            'player_id' => $amosBuy->id,
            'deck' => 'Kesempatan',
        ])->assertOk();

        $this->assertDatabaseHas('players', [
            'id' => $amosBuy->id,
            'pending_space_action->action' => 'buy_property',
            'pending_space_action->game_property_id' => $indonesia->id,
        ]);
    }

    public function test_draw_card_still_works_when_deck_exhausted(): void
    {
        $this->seed();
        $gameId = $this->createGame(50000);
        $game = Game::query()->with('players')->findOrFail($gameId);
        $amos = $game->players->firstWhere('name', 'Amos');
        $budi = $game->players->firstWhere('name', 'Budi');
        $cards = GameCard::query()->where('deck', 'Dana Umum')->where('is_active', true)->get();

        foreach ($cards as $card) {
            GameCardDraw::query()->create([
                'game_id' => $gameId,
                'game_card_id' => $card->id,
                'player_id' => $amos->id,
                'deck' => 'Dana Umum',
                'status' => 'held',
                'snapshot' => ['key' => $card->key, 'title' => $card->title],
                'resolved_at' => now(),
            ]);
        }

        $this->postJson("/api/games/{$gameId}/cards/draw", [
            'player_id' => $budi->id,
            'deck' => 'Dana Umum',
        ])->assertOk();

        $this->assertDatabaseHas('transactions', [
            'game_id' => $gameId,
            'type' => 'card_deck_reshuffled',
        ]);
        $this->assertDatabaseHas('transactions', [
            'game_id' => $gameId,
            'type' => 'card_draw',
            'to_player_id' => $budi->id,
        ]);
    }

    public function test_utility_rent_uses_payer_full_group_count(): void
    {
        $this->seed();
        $gameId = $this->createGame(500000);
        $game = Game::query()->with('players', 'gameProperties.property')->findOrFail($gameId);
        $amos = $game->players->firstWhere('name', 'Amos');
        $budi = $game->players->firstWhere('name', 'Budi');
        $utility = $game->gameProperties->first(fn ($item) => $item->property->property_kind === 'utility');
        $groupA = $game->gameProperties->filter(fn ($item) => $item->property->group_code === 'A');

        $this->postJson("/api/games/{$gameId}/transactions/buy-property", [
            'player_id' => $budi->id,
            'game_property_id' => $utility->id,
        ])->assertOk();

        foreach ($groupA as $property) {
            $this->postJson("/api/games/{$gameId}/transactions/buy-property", [
                'player_id' => $amos->id,
                'game_property_id' => $property->id,
            ])->assertOk();
        }

        $this->postJson("/api/games/{$gameId}/transactions/pay-rent", [
            'player_id' => $amos->id,
            'game_property_id' => $utility->id,
            'source' => 'bank',
        ])->assertOk();

        $this->assertDatabaseHas('transactions', [
            'game_id' => $gameId,
            'type' => 'pay_rent',
            'from_player_id' => $amos->id,
            'to_player_id' => $budi->id,
            'amount' => 30000,
        ]);
    }

    public function test_player_portal_shows_finished_game_state(): void
    {
        $this->seed();
        $gameId = $this->createGame(50000);
        $game = Game::query()->with('players', 'playerAccessTokens')->findOrFail($gameId);
        $amos = $game->players->firstWhere('name', 'Amos');
        $token = $game->playerAccessTokens->firstWhere('player_id', $amos->id);

        $this->postJson("/api/games/{$gameId}/finish")->assertOk();

        $this->getJson("/api/player/{$token->token}/state")
            ->assertOk()
            ->assertJsonPath('game.status', 'finished')
            ->assertJsonPath('player.name', 'Amos')
            ->assertJsonCount(2, 'players');
    }

    public function test_live_view_is_read_only_and_can_load_game_state(): void
    {
        $this->seed();
        $gameId = $this->createGame(50000);

        $this->get("/live/{$gameId}")
            ->assertOk()
            ->assertSee('Layar Penonton');

        $this->getJson("/api/live/{$gameId}")
            ->assertOk()
            ->assertJsonPath('state.game.id', $gameId)
            ->assertJsonCount(2, 'state.players');
    }

    private function createGame(int $startingBalance = 15000, int $startingCash = 0): int
    {
        return $this->postJson('/api/games', [
            'starting_balance' => $startingBalance,
            'starting_cash' => $startingCash,
            'duration_minutes' => 60,
            'players' => [
                ['name' => 'Amos', 'rfid_uid' => 'rfid-amos'],
                ['name' => 'Budi', 'rfid_uid' => 'rfid-budi'],
            ],
        ])->json('state.game.id');
    }
}
