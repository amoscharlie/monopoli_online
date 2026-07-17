<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LiveViewController;
use App\Http\Controllers\PlayerPortalController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');
Route::get('/live/{game}', LiveViewController::class)->name('live.view');
Route::get('/player/{token}', PlayerPortalController::class)->name('player.portal');
