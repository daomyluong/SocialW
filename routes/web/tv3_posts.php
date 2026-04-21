<?php

use App\Http\Controllers\BookmarkController3;
use App\Http\Controllers\PostController3;
use App\Http\Controllers\StoryController3;
use Illuminate\Support\Facades\Route;

// TV3 (THANH): post CRUD, media attachment in posts, post visibility.
Route::get('/', [PostController3::class, 'index'])->name('home');

Route::get('/posts/{id}', [PostController3::class, 'show'])
    ->whereNumber('id')
    ->name('posts3.show');

Route::middleware('auth')->group(function (): void {
    Route::get('/posts/create', [PostController3::class, 'create'])->name('posts3.create');
    Route::post('/posts/store', [PostController3::class, 'store'])->name('posts3.store');
    Route::get('/my-posts', [PostController3::class, 'myPosts'])->name('posts3.myPosts');
    Route::delete('/posts/{id}', [PostController3::class, 'destroy'])->name('posts3.destroy');
    Route::get('/posts/{id}/edit', [PostController3::class, 'edit'])->name('posts3.edit');
    Route::put('/posts/{id}', [PostController3::class, 'update'])->name('posts3.update');

    Route::get('/notifications', [PostController3::class, 'notifications'])->name('notifications.index');
    Route::post('/stories3', [StoryController3::class, 'store'])->name('stories3.store');

    Route::post('/bookmarks/toggle/{postId}', [BookmarkController3::class, 'toggleBookmark'])->name('bookmarks.toggle');
    Route::get('/bookmarks', [BookmarkController3::class, 'index'])->name('bookmarks.index');
    Route::get('/bookmarks/folders', [BookmarkController3::class, 'getFolders'])->name('bookmarks.folders');
});
