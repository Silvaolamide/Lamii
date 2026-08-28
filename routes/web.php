<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ConnectionController;
use App\Http\Controllers\DiscoverController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\SafetyController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'))->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/auth/{provider}/redirect', [AuthController::class, 'redirectToProvider'])->where('provider', 'google')->name('social.redirect');
    Route::match(['get', 'post'], '/auth/{provider}/callback', [AuthController::class, 'handleProviderCallback'])->where('provider', 'google')->name('social.callback');
    Route::get('/forgot-password', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::prefix('onboarding')->name('onboarding.')->group(function () {
        Route::get('/profile', [OnboardingController::class, 'profile'])->name('profile');
        Route::post('/profile', [OnboardingController::class, 'saveProfile'])->name('profile.save');
        Route::get('/interests', [OnboardingController::class, 'interests'])->name('interests');
        Route::post('/interests', [OnboardingController::class, 'saveInterests'])->name('interests.save');
        Route::get('/privacy', [OnboardingController::class, 'privacy'])->name('privacy');
        Route::post('/privacy', [OnboardingController::class, 'savePrivacy'])->name('privacy.save');
    });
    Route::middleware('onboarding')->group(function () {
        Route::get('/discover', [DiscoverController::class, 'index'])->name('discover');
        Route::get('/discover/nearby', [DiscoverController::class, 'nearby'])->middleware('throttle:30,1')->name('discover.nearby');
        Route::post('/location', [LocationController::class, 'update'])->middleware('throttle:30,1')->name('location.update');
        Route::delete('/location', [LocationController::class, 'destroy'])->middleware('throttle:30,1')->name('location.destroy');
        Route::get('/connections', [ConnectionController::class, 'index'])->name('connections.index');
        Route::post('/connections/{user}', [ConnectionController::class, 'store'])->middleware('throttle:20,1')->name('connections.store');
        Route::post('/connections/{connection}/accept', [ConnectionController::class, 'accept'])->name('connections.accept');
        Route::post('/connections/{connection}/decline', [ConnectionController::class, 'decline'])->name('connections.decline');
        Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
        Route::post('/chat/{user}', [ChatController::class, 'start'])->name('chat.start');
        Route::get('/chat/conversations/{conversation}', [ChatController::class, 'show'])->name('chat.show');
        Route::get('/chat/conversations/{conversation}/messages', [ChatController::class, 'messages'])->middleware('throttle:120,1')->name('chat.messages');
        Route::post('/chat/conversations/{conversation}/messages', [ChatController::class, 'store'])->middleware('throttle:60,1')->name('chat.messages.store');
        Route::post('/safety/block/{user}', [SafetyController::class, 'block'])->middleware('throttle:20,1')->name('safety.block');
        Route::delete('/safety/block/{user}', [SafetyController::class, 'unblock'])->name('safety.unblock');
        Route::post('/safety/report/{user}', [SafetyController::class, 'report'])->middleware('throttle:10,1')->name('safety.report');
        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
        Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    });
});
