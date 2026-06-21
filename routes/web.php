<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\FriendSearchController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('feed.index')
        : view('welcome');
})->name('home');

Route::get('/dashboard', function () {
    return redirect()->route('feed.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/feed', [FeedController::class, 'index'])->name('feed.index');

    Route::get('/search', FriendSearchController::class)->name('search.friends');

    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
    Route::post('/posts/{post}/like', [LikeController::class, 'toggle'])->name('posts.like');
    Route::post('/posts/{post}/reactions', [ReactionController::class, 'storeForPost'])->name('posts.reactions.store');

    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('posts.comments.store');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    Route::get('/users/{user}', [UserProfileController::class, 'show'])->name('profile.show');
    Route::post('/users/{user}/follow', [UserProfileController::class, 'follow'])->name('users.follow');
    Route::delete('/users/{user}/follow', [UserProfileController::class, 'unfollow'])->name('users.unfollow');
    Route::patch('/users/{user}/role', [UserProfileController::class, 'updateRole'])->name('users.role.update');

    Route::get('/messages', [ChatController::class, 'index'])->name('messages.index');
    Route::get('/messages/{user}', [ChatController::class, 'show'])->name('messages.show');
    Route::post('/messages/{user}', [ChatController::class, 'store'])->name('messages.store');
    Route::post('/messages/{message}/reactions', [ReactionController::class, 'storeForMessage'])->name('messages.reactions.store');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
