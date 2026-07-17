<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\MonopolyBankService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RfidController extends Controller
{
    public function __construct(private MonopolyBankService $bank)
    {
    }

    public function store(Request $request): JsonResponse
    {
        if (! $request->has('uid') && $request->has('UID')) {
            $request->merge(['uid' => $request->input('UID')]);
        }

        $data = $request->validate([
            'uid' => ['required', 'string', 'max:120'],
            'game_id' => ['nullable', 'integer'],
        ]);

        $result = $this->bank->scanRfid($data['uid'], $data['game_id'] ?? null);

        return response()->json($result, $result['matched'] ? 200 : 404);
    }
}
