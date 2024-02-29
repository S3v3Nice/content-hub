<?php

use App\Http\Controllers\PostCategoryController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
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

Route::get('/auth/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::put('/settings/profile', [SettingsController::class, 'changeProfileSettings']);
    Route::put('/settings/security/username', [SettingsController::class, 'changeUsername']);
    Route::put('/settings/security/email', [SettingsController::class, 'changeEmail']);
    Route::put('/settings/security/password', [SettingsController::class, 'changePassword']);
    Route::post('/settings/security/email-verification', [SettingsController::class, 'sendEmailVerificationLink'])->middleware(['throttle:1,1']);

    Route::middleware('moderator')->group(function () {
        Route::get('/users', [UserController::class, 'get']);
    });

    Route::middleware('admin')->group(function () {
        Route::post('/users', [UserController::class, 'add']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'delete']);
        Route::delete('/users', [UserController::class, 'deleteMultiple']);

        Route::get('/post-categories', [PostCategoryController::class, 'get']);
        Route::post('/post-categories', [PostCategoryController::class, 'add']);
        Route::put('/post-categories/{id}', [PostCategoryController::class, 'update']);
        Route::delete('/post-categories/{id}', [PostCategoryController::class, 'delete']);
        Route::delete('/post-categories', [PostCategoryController::class, 'deleteMultiple']);
    });
});
