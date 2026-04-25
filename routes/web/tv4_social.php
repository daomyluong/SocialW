<?php

use App\Http\Controllers\HomeController4;
use App\Http\Controllers\InteractionController4;
use Illuminate\Support\Facades\Route;

// TV4 (QUYNH): follow, like, comment, notification/social interactions.
Route::get('/posts/{post}/load-more-comments', [InteractionController4::class, 'show'])->name('comments.show');

Route::middleware('auth')->group(function (): void {
    Route::post('/posts/{post}/like', [InteractionController4::class, 'like'])->name('posts.like');
    Route::post('/posts/{post}/comments', [InteractionController4::class, 'comment'])->name('comments.store');
    Route::delete('/comments/{comment}', [InteractionController4::class, 'destroyComment'])->name('comments.destroy');
    Route::post('/posts/{post}/share', [InteractionController4::class, 'share'])->name('posts.share');
    Route::post('/users/{user}/follow', [InteractionController4::class, 'toggleFollow'])->name('users.follow');
    Route::get('/suggestions', [HomeController4::class, 'allSuggestions'])->name('users.suggestions');
    Route::post('/report', [InteractionController4::class, 'report'])->name('report.store');
});
