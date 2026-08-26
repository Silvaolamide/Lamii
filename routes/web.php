<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DiscoverController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\PasswordResetController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('welcome'))->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
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

    Route::get('/discover', [DiscoverController::class, 'index'])->name('discover');
    Route::get('/discover/nearby', [DiscoverController::class, 'nearby'])->middleware('throttle:30,1')->name('discover.nearby');
    Route::post('/location', [LocationController::class, 'update'])->middleware('throttle:30,1')->name('location.update');
    Route::delete('/location', [LocationController::class, 'destroy'])->middleware('throttle:30,1')->name('location.destroy');
});
