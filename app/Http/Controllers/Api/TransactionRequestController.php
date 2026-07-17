<?php

namespace App\Http\Controllers\Api;

use App\Support\SafeBroadcast;
use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Services\MonopolyBankService;
use Illuminate\Http\JsonResponse;

class TransactionRequestController extends Controller
{
    public function __construct(private MonopolyBankService $bank)
    {
    }

    public function approve(Game $game, int $requestId): JsonResponse
    {
        $this->bank->approveRequest($game, $requestId);
        SafeBroadcast::gameUpdated($game->id);

        return response()->json([
            'message' => 'Permintaan pemain disetujui dan transaksi sudah diproses.',
            'state' => $this->bank->state($game->fresh()),
        ]);
    }

    public function reject(Game $game, int $requestId): JsonResponse
    {
        $this->bank->rejectRequest($game, $requestId);
        SafeBroadcast::gameUpdated($game->id);

        return response()->json([
            'message' => 'Permintaan pemain ditolak.',
            'state' => $this->bank->state($game->fresh()),
        ]);
    }
}
