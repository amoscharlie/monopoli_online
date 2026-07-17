<?php

namespace App\Http\Controllers;

use App\Models\PlayerAccessToken;
use Illuminate\Contracts\View\View;

class PlayerPortalController extends Controller
{
    public function __invoke(string $token): View
    {
        $accessToken = PlayerAccessToken::query()
            ->where('token', $token)
            ->whereNull('revoked_at')
            ->firstOrFail();

        return view('player-portal', [
            'token' => $accessToken->token,
            'playerName' => $accessToken->player?->name,
        ]);
    }
}
