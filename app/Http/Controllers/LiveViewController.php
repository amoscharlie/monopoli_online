<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Contracts\View\View;

class LiveViewController extends Controller
{
    public function __invoke(Game $game): View
    {
        return view('live-view', [
            'gameId' => $game->id,
            'gameCode' => $game->code,
        ]);
    }
}
