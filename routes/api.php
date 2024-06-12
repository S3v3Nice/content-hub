<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostCategoryController;
use App\Http\Controllers\PostCommentController;
use App\Http\Controllers\PostCommentLikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostLikeController;
use App\Http\Controllers\PostVersionController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UploadImageController;
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

Route::get('/auth/user', fn(Request $request) => $request->user());
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/logout', [AuthController::class, 'logout']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);

Route::get('/post-categories', [PostCategoryController::class, 'get']);

Route::get('/posts', [PostController::class, 'get']);
Route::get('/posts/{slug}', [PostController::class, 'getBySlug']);

Route::get('/posts/{postId}/comments', [PostCommentController::class, 'getByPostId'])->where('id', '[0-9]+');

Route::middleware('auth:sanctum')->group(function () {
    Route::put('/settings/profile', [SettingsController::class, 'changeProfileSettings']);
    Route::put('/settings/security/username', [SettingsController::class, 'changeUsername']);
    Route::put('/settings/security/email', [SettingsController::class, 'changeEmail']);
    Route::put('/settings/security/password', [SettingsController::class, 'changePassword']);
    Route::post('/settings/security/email-verification', [SettingsController::class, 'sendEmailVerificationLink'])->middleware(['throttle:1,1']);

    Route::post('/upload-image', [UploadImageController::class, 'upload']);

    Route::get('/post-versions/{id}', [PostVersionController::class, 'getById'])->where('id', '[0-9]+');
    Route::post('/post-versions', [PostVersionController::class, 'createDraft']);
    Route::post('/post-versions/submit', [PostVersionController::class, 'submitNew']);
    Route::patch('/post-versions/{id}', [PostVersionController::class, 'updateDraft'])->where('id', '[0-9]+');
    Route::patch('/post-versions/{id}/submit', [PostVersionController::class, 'submit'])->where('id', '[0-9]+');

    Route::get('/users/{userId}/post-versions', [PostVersionController::class, 'getByUser'])->where('userId', '[0-9]+');
    Route::get('/users/{userId}/posts', [PostController::class, 'getByUser'])->where('userId', '[0-9]+');

    Route::post('/posts/{postId}/likes', [PostLikeController::class, 'like'])->where('postId', '[0-9]+');
    Route::delete('/posts/{postId}/likes', [PostLikeController::class, 'unlike'])->where('postId', '[0-9]+');

    Route::post('/posts/{postId}/comments', [PostCommentController::class, 'submit'])->where('id', '[0-9]+');
    Route::delete('/post-comments/{id}', [PostCommentController::class, 'remove'])->where('id', '[0-9]+');
    Route::patch('/post-comments/{id}', [PostCommentController::class, 'edit'])->where('id', '[0-9]+');

    Route::post('/post-comments/{id}/likes', [PostCommentLikeController::class, 'like'])->where('id', '[0-9]+');
    Route::delete('/post-comments/{id}/likes', [PostCommentLikeController::class, 'unlike'])->where('id', '[0-9]+');

    Route::middleware('moderator')->group(function () {
        Route::get('/users', [UserController::class, 'get']);

        Route::get('/post-versions', [PostVersionController::class, 'get']);
        Route::put('/post-versions/{id}/assigned-moderator', [PostVersionController::class, 'assignModerator'])->where('id', '[0-9]+');
        Route::patch('/post-versions/{id}/request-changes', [PostVersionController::class, 'requestChanges'])->where('id', '[0-9]+');
        Route::patch('/post-versions/{id}/accept', [PostVersionController::class, 'accept'])->where('id', '[0-9]+');
        Route::patch('/post-versions/{id}/reject', [PostVersionController::class, 'reject'])->where('id', '[0-9]+');
    });

    Route::middleware('admin')->group(function () {
        Route::post('/users', [UserController::class, 'add']);
        Route::put('/users/{id}', [UserController::class, 'update'])->where('id', '[0-9]+');;
        Route::delete('/users/{id}', [UserController::class, 'delete'])->where('id', '[0-9]+');;
        Route::delete('/users', [UserController::class, 'deleteMultiple']);

        Route::post('/post-categories', [PostCategoryController::class, 'add']);
        Route::put('/post-categories/{id}', [PostCategoryController::class, 'update'])->where('id', '[0-9]+');;
        Route::delete('/post-categories/{id}', [PostCategoryController::class, 'delete'])->where('id', '[0-9]+');;
        Route::delete('/post-categories', [PostCategoryController::class, 'deleteMultiple']);
    });
});
