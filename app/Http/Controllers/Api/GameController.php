<?php

namespace App\Http\Controllers\Api;

use App\Support\SafeBroadcast;
use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\Property;
use App\Services\MonopolyBankService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class GameController extends Controller
{
    public function __construct(private MonopolyBankService $bank)
    {
    }

    public function meta(): JsonResponse
    {
        return response()->json([
            'settings' => $this->bank->settingsPayload(),
            'properties' => $this->propertyCatalog(),
            'active_games' => $this->bank->openGames(),
            'history_games' => $this->bank->historyGames(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'starting_balance' => ['required', 'integer', 'min:1'],
            'players' => ['required', 'array', 'min:2', 'max:8'],
            'players.*.name' => ['required', 'string', 'max:80'],
            'players.*.rfid_uid' => ['nullable', 'string', 'max:120'],
            'duration_minutes' => ['nullable', 'integer', 'in:60,120,180'],
            'starting_cash' => ['nullable', 'integer', 'min:0'],
        ]);

        $uids = collect($data['players'])
            ->pluck('rfid_uid')
            ->filter(fn ($uid) => filled($uid))
            ->map(fn (string $uid) => strtoupper(str_replace(' ', '', trim($uid))));
        if ($uids->unique()->count() !== $uids->count()) {
            throw ValidationException::withMessages(['players' => 'UID RFID setiap pemain harus unik.']);
        }

        $game = $this->bank->createGame($data);
        SafeBroadcast::gameUpdated($game->id);

        return response()->json([
            'message' => 'Game baru berhasil dimulai.',
            'state' => $this->bank->state($game),
        ], 201);
    }

    public function show(Game $game): JsonResponse
    {
        return response()->json([
            'state' => $this->bank->state($game),
        ]);
    }

    public function pause(Game $game): JsonResponse
    {
        $this->bank->pause($game);

        return $this->stateResponse($game, 'Game dijeda.');
    }

    public function resume(Game $game): JsonResponse
    {
        $this->bank->resume($game);

        return $this->stateResponse($game, 'Game dilanjutkan.');
    }

    public function reset(Game $game): JsonResponse
    {
        $this->bank->reset($game);

        return $this->stateResponse($game, 'Game saat ini sudah di-reset.');
    }

    public function finish(Game $game): JsonResponse
    {
        $this->bank->finish($game);

        return $this->stateResponse($game, 'Game selesai dan pemenang sudah dihitung.');
    }

    public function refreshCards(Game $game): JsonResponse
    {
        $this->bank->refreshCards($game);

        return $this->stateResponse($game, 'Semua kartu Dana Umum & Kesempatan bisa dipakai lagi.');
    }

    public function drawCard(Request $request, Game $game): JsonResponse
    {
        $data = $request->validate([
            'player_id' => ['required', 'integer'],
            'deck' => ['required', 'string', 'in:Dana Umum,Kesempatan'],
        ]);

        $draw = $this->bank->drawOnlineCard($game, $data['player_id'], $data['deck']);

        return $this->stateResponse($game, "{$draw->player?->name} mendapat kartu {$draw->card?->deck}: {$draw->card?->title}.");
    }

    public function setFirstPlayer(Request $request, Game $game): JsonResponse
    {
        $data = $request->validate([
            'player_id' => ['required', 'integer'],
        ]);

        $this->bank->setFirstPlayer($game, $data['player_id']);

        return $this->stateResponse($game, 'Pemain pertama berhasil ditetapkan.');
    }

    public function destroy(Game $game): JsonResponse
    {
        if ($game->status === 'finished') {
            throw ValidationException::withMessages([
                'game' => 'Game yang sudah selesai tidak bisa dihapus dari Continue Game.',
            ]);
        }

        $game->delete();

        return response()->json([
            'message' => 'Continue game berhasil dihapus.',
            'active_games' => $this->bank->openGames(),
            'history_games' => $this->bank->historyGames(),
        ]);
    }

    public function destroyHistory(Game $game): JsonResponse
    {
        if ($game->status !== 'finished') {
            throw ValidationException::withMessages([
                'game' => 'Hanya history game yang sudah selesai yang bisa dihapus dari halaman History.',
            ]);
        }

        $game->delete();

        return response()->json([
            'message' => 'History game berhasil dihapus.',
            'active_games' => $this->bank->openGames(),
            'history_games' => $this->bank->historyGames(),
        ]);
    }

    private function stateResponse(Game $game, string $message): JsonResponse
    {
        SafeBroadcast::gameUpdated($game->id);

        return response()->json([
            'message' => $message,
            'state' => $this->bank->state($game->fresh()),
            'active_games' => $this->bank->openGames(),
            'history_games' => $this->bank->historyGames(),
        ]);
    }

    private function propertyCatalog(): array
    {
        return Property::query()
            ->orderBy('sort_order')
            ->get()
            ->map(fn (Property $property) => [
                'id' => $property->id,
                'name' => $property->name,
                'price' => $property->price,
                'house_price' => $property->house_price,
                'hotel_price' => $property->hotel_price,
                'rent' => $property->rent,
                'rent_1_house' => $property->rent_1_house,
                'rent_2_houses' => $property->rent_2_houses,
                'rent_3_houses' => $property->rent_3_houses,
                'rent_4_houses' => $property->rent_4_houses,
                'rent_hotel' => $property->rent_hotel,
                'color' => $property->color,
                'sort_order' => $property->sort_order,
            ])
            ->values()
            ->all();
    }
}
