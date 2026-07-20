<?php

use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\LiveViewController;
use App\Http\Controllers\Api\PlayerPortalController;
use App\Http\Controllers\Api\RfidController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\TransactionRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/games/meta', [GameController::class, 'meta']);
Route::post('/games', [GameController::class, 'store']);
Route::get('/games/{game}', [GameController::class, 'show']);
Route::get('/live/{game}', [LiveViewController::class, 'show']);
Route::delete('/games/{game}', [GameController::class, 'destroy']);
Route::delete('/games/{game}/history', [GameController::class, 'destroyHistory']);
Route::post('/games/{game}/pause', [GameController::class, 'pause']);
Route::post('/games/{game}/resume', [GameController::class, 'resume']);
Route::post('/games/{game}/reset', [GameController::class, 'reset']);
Route::post('/games/{game}/finish', [GameController::class, 'finish']);
Route::post('/games/{game}/cards/refresh', [GameController::class, 'refreshCards']);
Route::post('/games/{game}/cards/draw', [GameController::class, 'drawCard']);
Route::post('/games/{game}/first-player', [GameController::class, 'setFirstPlayer']);
Route::post('/games/{game}/requests/{requestId}/approve', [TransactionRequestController::class, 'approve']);
Route::post('/games/{game}/requests/{requestId}/reject', [TransactionRequestController::class, 'reject']);

Route::get('/player/{token}/state', [PlayerPortalController::class, 'state']);
Route::post('/player/{token}/requests', [PlayerPortalController::class, 'requestTransaction']);
Route::post('/player/{token}/rent-preview', [PlayerPortalController::class, 'rentPreview']);
Route::post('/player/{token}/pay-rent', [PlayerPortalController::class, 'payRent']);
Route::post('/player/{token}/roll-dice', [PlayerPortalController::class, 'rollDice']);
Route::post('/player/{token}/space-action', [PlayerPortalController::class, 'resolveSpaceAction']);
Route::post('/player/{token}/jail-card-transfers/{transferId}', [PlayerPortalController::class, 'decideJailCardTransfer']);
Route::post('/player/{token}/jail-card-transfers', [PlayerPortalController::class, 'offerJailCardTransfer']);

Route::post('/games/{game}/transactions/transfer', [TransactionController::class, 'transfer']);
Route::post('/games/{game}/transactions/pay-rent', [TransactionController::class, 'payRent']);
Route::post('/games/{game}/transactions/collect-from-players', [TransactionController::class, 'collectFromPlayers']);
Route::post('/games/{game}/transactions/deposit', [TransactionController::class, 'deposit']);
Route::post('/games/{game}/transactions/withdraw', [TransactionController::class, 'withdraw']);
Route::post('/games/{game}/transactions/bank-to-player', [TransactionController::class, 'bankToPlayer']);
Route::post('/games/{game}/transactions/player-to-bank', [TransactionController::class, 'playerToBank']);
Route::post('/games/{game}/transactions/buy-property', [TransactionController::class, 'buyProperty']);
Route::post('/games/{game}/transactions/sell-property', [TransactionController::class, 'sellProperty']);
Route::post('/games/{game}/transactions/transfer-property', [TransactionController::class, 'transferProperty']);
Route::post('/games/{game}/transactions/add-house', [TransactionController::class, 'addHouse']);
Route::post('/games/{game}/transactions/sell-house', [TransactionController::class, 'sellHouse']);
Route::post('/games/{game}/transactions/add-hotel', [TransactionController::class, 'addHotel']);
Route::post('/games/{game}/transactions/sell-hotel', [TransactionController::class, 'sellHotel']);
Route::post('/games/{game}/transactions/auction', [TransactionController::class, 'auction']);
Route::post('/games/{game}/transactions/bankruptcy-preview', [TransactionController::class, 'bankruptcyPreview']);
Route::post('/games/{game}/transactions/bankrupt', [TransactionController::class, 'bankrupt']);

Route::post('/rfid', [RfidController::class, 'store']);

Route::get('/settings', [SettingsController::class, 'index']);
Route::put('/settings', [SettingsController::class, 'update']);
Route::post('/settings/properties', [SettingsController::class, 'storeProperty']);
Route::put('/settings/properties/{property}', [SettingsController::class, 'updateProperty']);
Route::delete('/settings/properties/{property}', [SettingsController::class, 'destroyProperty']);
Route::post('/settings/properties/import', [SettingsController::class, 'importProperties']);
Route::get('/settings/properties/export', [SettingsController::class, 'exportProperties']);
