<?php

use App\Http\Controllers\SettingsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('admin')->group(function () {
    });

    Route::put('/settings/profile', [SettingsController::class, 'changeProfileSettings']);
    Route::put('/settings/security/username', [SettingsController::class, 'changeUsername']);
    Route::put('/settings/security/email', [SettingsController::class, 'changeEmail']);
    Route::put('/settings/security/password', [SettingsController::class, 'changePassword']);
    Route::post('/settings/security/email-verification', [SettingsController::class, 'sendEmailVerificationLink'])->middleware(['throttle:1,1']);
});
