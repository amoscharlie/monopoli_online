<?php

namespace App\Http\Controllers\Api;

use App\Support\SafeBroadcast;
use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Services\MonopolyBankService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(private MonopolyBankService $bank)
    {
    }

    public function transfer(Request $request, Game $game): JsonResponse
    {
        $data = $request->validate([
            'from_player_id' => ['required', 'integer'],
            'to_player_id' => ['required', 'integer'],
            'amount' => ['required', 'integer', 'min:1'],
            'source' => ['nullable', 'string', 'in:bank,cash'],
        ]);

        $this->bank->transfer($game, $data['from_player_id'], $data['to_player_id'], $data['amount'], $data['source'] ?? 'bank');

        return $this->stateResponse($game, 'Transfer berhasil.');
    }

    public function payRent(Request $request, Game $game): JsonResponse
    {
        $data = $request->validate([
            'player_id' => ['required', 'integer'],
            'game_property_id' => ['required', 'integer'],
            'source' => ['nullable', 'string', 'in:bank,cash'],
        ]);

        $this->bank->payRent($game, $data['player_id'], $data['game_property_id'], $data['source'] ?? 'bank');

        return $this->stateResponse($game, 'Sewa berhasil dibayar.');
    }

    public function collectFromPlayers(Request $request, Game $game): JsonResponse
    {
        $data = $request->validate([
            'player_id' => ['required', 'integer'],
            'amount' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:120'],
            'card_key' => ['nullable', 'string', 'max:120'],
        ]);

        $this->bank->collectFromPlayers($game, $data['player_id'], $data['amount'], $data['reason'] ?? null, $data['card_key'] ?? null);

        return $this->stateResponse($game, 'Uang dari semua pemain berhasil diterima.');
    }

    public function deposit(Request $request, Game $game): JsonResponse
    {
        $data = $request->validate([
            'player_id' => ['required', 'integer'],
            'amount' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:120'],
        ]);

        $this->bank->deposit($game, $data['player_id'], $data['amount'], $data['reason'] ?? null);

        return $this->stateResponse($game, 'Deposit berhasil.');
    }

    public function withdraw(Request $request, Game $game): JsonResponse
    {
        $data = $request->validate([
            'player_id' => ['required', 'integer'],
            'amount' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:120'],
        ]);

        $this->bank->withdraw($game, $data['player_id'], $data['amount'], $data['reason'] ?? null);

        return $this->stateResponse($game, 'Withdraw berhasil.');
    }

    public function bankToPlayer(Request $request, Game $game): JsonResponse
    {
        $data = $request->validate([
            'player_id' => ['required', 'integer'],
            'amount' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:120'],
            'card_key' => ['nullable', 'string', 'max:120'],
        ]);

        $this->bank->bankToPlayer($game, $data['player_id'], $data['amount'], $data['reason'] ?? null, $data['card_key'] ?? null);

        return $this->stateResponse($game, 'Bank memberi uang ke pemain.');
    }

    public function playerToBank(Request $request, Game $game): JsonResponse
    {
        $data = $request->validate([
            'player_id' => ['required', 'integer'],
            'amount' => ['required', 'integer', 'min:1'],
            'reason' => ['nullable', 'string', 'max:120'],
            'card_key' => ['nullable', 'string', 'max:120'],
        ]);

        $this->bank->playerToBank($game, $data['player_id'], $data['amount'], $data['reason'] ?? null, $data['card_key'] ?? null);

        return $this->stateResponse($game, 'Pembayaran ke Bank berhasil.');
    }

    public function buyProperty(Request $request, Game $game): JsonResponse
    {
        $data = $request->validate([
            'player_id' => ['required', 'integer'],
            'game_property_id' => ['required', 'integer'],
        ]);

        $this->bank->buyProperty($game, $data['player_id'], $data['game_property_id']);

        return $this->stateResponse($game, 'Properti berhasil dibeli.');
    }

    public function sellProperty(Request $request, Game $game): JsonResponse
    {
        $data = $request->validate([
            'player_id' => ['required', 'integer'],
            'game_property_id' => ['required', 'integer'],
        ]);

        $this->bank->sellProperty($game, $data['player_id'], $data['game_property_id']);

        return $this->stateResponse($game, 'Properti berhasil dijual ke Bank.');
    }

    public function transferProperty(Request $request, Game $game): JsonResponse
    {
        $data = $request->validate([
            'from_player_id' => ['required', 'integer'],
            'to_player_id' => ['required', 'integer'],
            'game_property_id' => ['required', 'integer'],
        ]);

        $this->bank->transferProperty($game, $data['from_player_id'], $data['to_player_id'], $data['game_property_id']);

        return $this->stateResponse($game, 'Properti berhasil dipindahkan.');
    }

    public function addHouse(Request $request, Game $game): JsonResponse
    {
        $data = $request->validate([
            'player_id' => ['required', 'integer'],
            'game_property_id' => ['required', 'integer'],
        ]);

        $this->bank->addHouse($game, $data['player_id'], $data['game_property_id']);

        return $this->stateResponse($game, 'Rumah berhasil ditambahkan.');
    }

    public function sellHouse(Request $request, Game $game): JsonResponse
    {
        $data = $request->validate([
            'player_id' => ['required', 'integer'],
            'game_property_id' => ['required', 'integer'],
        ]);

        $this->bank->sellHouse($game, $data['player_id'], $data['game_property_id']);

        return $this->stateResponse($game, 'Rumah berhasil dijual ke Bank.');
    }

    public function addHotel(Request $request, Game $game): JsonResponse
    {
        $data = $request->validate([
            'player_id' => ['required', 'integer'],
            'game_property_id' => ['required', 'integer'],
        ]);

        $this->bank->addHotel($game, $data['player_id'], $data['game_property_id']);

        return $this->stateResponse($game, 'Hotel berhasil ditambahkan.');
    }

    public function sellHotel(Request $request, Game $game): JsonResponse
    {
        $data = $request->validate([
            'player_id' => ['required', 'integer'],
            'game_property_id' => ['required', 'integer'],
        ]);

        $this->bank->sellHotel($game, $data['player_id'], $data['game_property_id']);

        return $this->stateResponse($game, 'Hotel berhasil dijual ke Bank.');
    }

    public function auction(Request $request, Game $game): JsonResponse
    {
        $data = $request->validate([
            'player_id' => ['required', 'integer'],
            'game_property_id' => ['required', 'integer'],
            'amount' => ['required', 'integer', 'min:1'],
        ]);

        $this->bank->auctionProperty($game, $data['player_id'], $data['game_property_id'], $data['amount']);

        return $this->stateResponse($game, 'Lelang properti berhasil.');
    }

    public function bankruptcyPreview(Request $request, Game $game): JsonResponse
    {
        $data = $request->validate([
            'player_id' => ['required', 'integer'],
        ]);

        return response()->json([
            'summary' => $this->bank->bankruptcyPreview($game, $data['player_id']),
        ]);
    }

    public function bankrupt(Request $request, Game $game): JsonResponse
    {
        $data = $request->validate([
            'player_id' => ['required', 'integer'],
        ]);

        $this->bank->bankruptPlayer($game, $data['player_id']);

        return $this->stateResponse($game, 'Pemain ditandai bangkrut.');
    }

    private function stateResponse(Game $game, string $message): JsonResponse
    {
        SafeBroadcast::gameUpdated($game->id);

        return response()->json([
            'message' => $message,
            'state' => $this->bank->state($game->fresh()),
        ]);
    }
}
