<?php

use App\Http\Controllers\API\FeedController;
use App\Http\Controllers\API\LikeController;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/posts', [PostController::class, 'index']);
Route::get('/feed', [FeedController::class, 'index']);
Route::get('/users/{user}', [UserController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/posts', [PostController::class, 'store']);
    Route::post('/like', [LikeController::class, 'store']);
});
