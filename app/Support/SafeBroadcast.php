<?php

namespace App\Support;

use App\Events\GameStateUpdated;
use Throwable;

class SafeBroadcast
{
    public static function gameUpdated(int $gameId): void
    {
        try {
            $pendingBroadcast = broadcast(new GameStateUpdated($gameId));
            unset($pendingBroadcast);
        } catch (Throwable) {
            // Reverb is optional during local play. Polling keeps screens updated.
        }
    }
}
