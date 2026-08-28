<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NearbyController;
use App\Http\Controllers\WaveController;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('app'));
Route::get('/auth/{provider}/redirect', [AuthController::class, 'redirect'])->where('provider','google|apple|x');
Route::get('/auth/{provider}/callback', [AuthController::class, 'callback'])->where('provider','google|apple|x');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth');
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => view('app'))->name('dashboard');
    Route::get('/api/nearby', [NearbyController::class, 'index']);
    Route::post('/api/location', [NearbyController::class, 'updateLocation']);
    Route::post('/api/visibility', [NearbyController::class, 'visibility']);
    Route::get('/api/waves/incoming', [WaveController::class, 'incoming']);
    Route::post('/api/waves', [WaveController::class, 'store']);
    Route::post('/api/waves/{wave}/respond', [WaveController::class, 'respond']);
    Route::get('/api/connections', [WaveController::class, 'connections']);
    Route::get('/api/chats/{user}', [ChatController::class, 'show']);
    Route::post('/api/chats/{user}/messages', [ChatController::class, 'store']);
});
