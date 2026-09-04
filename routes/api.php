<?php

use App\Http\Controllers\Api\V1\ChatController;
use App\Http\Controllers\Api\V1\ConnectionController;
use App\Http\Controllers\Api\V1\DiscoverController;
use App\Http\Controllers\Api\V1\LocationController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\SafetyController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [MeController::class, 'show']);
    Route::patch('/me', [MeController::class, 'update']);

    Route::middleware('onboarding')->group(function () {
        Route::get('/discover/nearby', [DiscoverController::class, 'nearby'])->middleware('throttle:30,1');
        Route::post('/location', [LocationController::class, 'update'])->middleware('throttle:30,1');
        Route::delete('/location', [LocationController::class, 'destroy'])->middleware('throttle:30,1');
        Route::get('/connections', [ConnectionController::class, 'index']);
        Route::post('/users/{user}/wave', [ConnectionController::class, 'wave'])->middleware('throttle:20,1');
        Route::post('/connections/{connection}/accept', [ConnectionController::class, 'accept']);
        Route::post('/connections/{connection}/decline', [ConnectionController::class, 'decline']);
        Route::get('/conversations', [ChatController::class, 'index']);
        Route::post('/conversations/{user}', [ChatController::class, 'start']);
        Route::get('/conversations/{conversation}/messages', [ChatController::class, 'messages'])->middleware('throttle:120,1');
        Route::post('/conversations/{conversation}/messages', [ChatController::class, 'store'])->middleware('throttle:60,1');
        Route::get('/chat/unread-count', [ChatController::class, 'unreadCount'])->middleware('throttle:120,1');
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/{id}/read', [NotificationController::class, 'read']);
        Route::post('/notifications/read-all', [NotificationController::class, 'readAll']);
        Route::post('/users/{user}/block', [SafetyController::class, 'block'])->middleware('throttle:20,1');
        Route::delete('/users/{user}/block', [SafetyController::class, 'unblock']);
        Route::post('/users/{user}/report', [SafetyController::class, 'report'])->middleware('throttle:10,1');
    });
});
