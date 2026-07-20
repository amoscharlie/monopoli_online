<?php

namespace App\Http\Controllers\Api;

use App\Support\SafeBroadcast;
use App\Http\Controllers\Controller;
use App\Models\PlayerAccessToken;
use App\Models\TransactionRequest;
use App\Services\MonopolyBankService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlayerPortalController extends Controller
{
    public function __construct(private MonopolyBankService $bank)
    {
    }

    public function state(string $token): JsonResponse
    {
        $accessToken = $this->accessToken($token);
        $accessToken->update(['last_seen_at' => now()]);
        $state = $this->bank->state($accessToken->game);
        $player = collect($state['players'])->firstWhere('id', $accessToken->player_id);

        return response()->json([
            'game' => $state['game'] + ['turn' => $state['turn']],
            'player' => $player,
            'players' => $state['players'],
            'properties' => $state['properties'],
            'transactions' => collect($state['transactions'])
                ->filter(fn ($transaction) => $this->belongsToPlayer($transaction, $accessToken->player_id))
                ->values()
                ->all(),
            'requests' => TransactionRequest::query()
                ->where('game_id', $accessToken->game_id)
                ->where('player_id', $accessToken->player_id)
                ->latest()
                ->take(20)
                ->get()
                ->map(fn (TransactionRequest $request) => [
                    'id' => $request->id,
                    'type' => $request->type,
                    'status' => $request->status,
                    'reason' => $request->reason,
                    'amount' => $request->amount,
                    'created_at_label' => $request->created_at?->format('H:i:s'),
                ])
                ->values()
                ->all(),
            'jail_card_transfers' => collect($state['jail_card_transfers'] ?? [])
                ->filter(fn ($transfer) => (int) $transfer['to_player_id'] === (int) $accessToken->player_id || (int) $transfer['from_player_id'] === (int) $accessToken->player_id)
                ->values()
                ->all(),
        ]);
    }

    public function requestTransaction(Request $request, string $token): JsonResponse
    {
        $accessToken = $this->accessToken($token);
        $data = $request->validate([
            'type' => ['required', 'string', 'in:transfer,player_to_bank,bank_to_player,deposit,withdraw,buy_property,sell_property,add_house,sell_house,add_hotel,sell_hotel,pay_jail_fee,use_jail_card,bulk_sell_assets'],
            'target_player_id' => ['nullable', 'integer'],
            'game_property_id' => ['nullable', 'integer'],
            'amount' => ['nullable', 'integer', 'min:1'],
            'source' => ['nullable', 'string', 'in:bank,cash'],
            'reason' => ['nullable', 'string', 'max:160'],
            'owner_id' => ['nullable', 'integer'],
            'bulk_total' => ['nullable', 'integer', 'min:0'],
            'liquidation_plan' => ['nullable', 'array'],
            'liquidation_plan.*.type' => ['nullable', 'string', 'in:sell_property,sell_house,sell_hotel'],
            'liquidation_plan.*.game_property_id' => ['nullable', 'integer'],
            'liquidation_plan.*.quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $transactionRequest = $this->bank->submitPlayerRequest($accessToken, $data);
        SafeBroadcast::gameUpdated($accessToken->game_id);

        return response()->json([
            'message' => 'Permintaan dikirim ke Bank. Tunggu persetujuan admin.',
            'request_id' => $transactionRequest->id,
        ], 201);
    }

    public function rentPreview(Request $request, string $token): JsonResponse
    {
        $accessToken = $this->accessToken($token);
        $data = $request->validate([
            'game_property_id' => ['required', 'integer'],
            'source' => ['nullable', 'string', 'in:bank,cash'],
        ]);

        return response()->json([
            'preview' => $this->bank->rentPreview($accessToken->game, $accessToken->player_id, $data['game_property_id'], $data['source'] ?? 'bank'),
        ]);
    }

    public function payRent(Request $request, string $token): JsonResponse
    {
        $accessToken = $this->accessToken($token);
        $data = $request->validate([
            'game_property_id' => ['required', 'integer'],
            'source' => ['nullable', 'string', 'in:bank,cash'],
        ]);

        $transaction = $this->bank->payRent($accessToken->game, $accessToken->player_id, $data['game_property_id'], $data['source'] ?? 'bank');
        SafeBroadcast::gameUpdated($accessToken->game_id);

        return response()->json([
            'message' => $transaction->type === 'rent_bankruptcy'
                ? 'Kamu bangkrut karena uang dan semua aset tidak cukup membayar sewa. Sisa nilai sudah dibayarkan ke pemilik.'
                : 'Sewa berhasil dibayar.',
            'transaction_id' => $transaction->id,
        ]);
    }

    public function rollDice(string $token): JsonResponse
    {
        $accessToken = $this->accessToken($token);
        $roll = $this->bank->rollDice($accessToken->game, $accessToken->player_id);
        SafeBroadcast::gameUpdated($accessToken->game_id);

        return response()->json([
            'message' => 'Dadu berhasil dikocok.',
            'roll' => [
                'id' => $roll->id,
                'dice_one' => $roll->dice_one,
                'dice_two' => $roll->dice_two,
                'total' => $roll->total,
                'is_double' => $roll->is_double,
                'result' => $roll->result,
            ],
        ]);
    }

    public function resolveSpaceAction(Request $request, string $token): JsonResponse
    {
        $accessToken = $this->accessToken($token);
        $data = $request->validate([
            'decision' => ['required', 'string', 'in:buy,pay,skip,draw_chance,add_house,add_hotel,sell_house,sell_hotel'],
            'source' => ['nullable', 'string', 'in:bank,cash'],
        ]);

        $this->bank->resolveSpaceAction($accessToken->game, $accessToken->player_id, $data['decision'], $data['source'] ?? 'bank');
        SafeBroadcast::gameUpdated($accessToken->game_id);

        return response()->json([
            'message' => 'Aksi petak selesai.',
        ]);
    }

    public function decideJailCardTransfer(Request $request, string $token, int $transferId): JsonResponse
    {
        $accessToken = $this->accessToken($token);
        $data = $request->validate([
            'approve' => ['required', 'boolean'],
        ]);

        $this->bank->decideJailFreeCardTransfer($accessToken->game, $accessToken->player_id, $transferId, (bool) $data['approve']);
        SafeBroadcast::gameUpdated($accessToken->game_id);

        return response()->json([
            'message' => $data['approve'] ? 'Kartu bebas penjara berhasil dibeli.' : 'Penawaran kartu ditolak.',
        ]);
    }

    public function offerJailCardTransfer(Request $request, string $token): JsonResponse
    {
        $accessToken = $this->accessToken($token);
        $data = $request->validate([
            'to_player_id' => ['required', 'integer'],
        ]);

        $transfer = $this->bank->transferJailFreeCard($accessToken->game, $accessToken->player_id, $data['to_player_id']);
        SafeBroadcast::gameUpdated($accessToken->game_id);

        return response()->json([
            'message' => 'Penawaran kartu bebas penjara dikirim.',
            'transfer_id' => $transfer->id,
        ], 201);
    }

    private function accessToken(string $token): PlayerAccessToken
    {
        return PlayerAccessToken::query()
            ->where('token', $token)
            ->whereNull('revoked_at')
            ->with(['game', 'player'])
            ->firstOrFail();
    }

    private function belongsToPlayer(array $transaction, int $playerId): bool
    {
        return (int) ($transaction['from_player_id'] ?? 0) === $playerId
            || (int) ($transaction['to_player_id'] ?? 0) === $playerId;
    }
}
