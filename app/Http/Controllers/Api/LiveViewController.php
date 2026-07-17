<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Services\MonopolyBankService;
use Illuminate\Http\JsonResponse;

class LiveViewController extends Controller
{
    public function __construct(private MonopolyBankService $bank)
    {
    }

    public function show(Game $game): JsonResponse
    {
        return response()->json([
            'state' => $this->bank->state($game),
        ]);
    }
}
