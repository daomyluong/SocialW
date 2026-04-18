<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\InteractionController4;

Route::get('/', function () {
    return view('welcome');
});

// TV 1 (ĐÀO) - HẠ TẦNG, TRANG CHỦ & TÌM KIẾM
Route::get('/', function () {
    return view('home'); 
})->name('home');

Route::get('/search', function () {
    return "Trang tìm kiếm";
})->name('search');

// TV 2 (LOAN) - NGƯỜI DÙNG (AUTH & PROFILE)
Route::get('/profile', function () {
    return "Trang profile";
})->name('profile');

// TV 3 (THANH) - BÀI VIẾT (POSTS)
// TV 4 (QUỲNH) - TƯƠNG TÁC (SOCIAL)


Route::middleware(['auth'])->group(function () {
    // Like
    Route::post('/posts/{post}/like', [InteractionController4::class, 'like'])->name('posts.like');

    // Comment
    Route::post('/posts/{post}/comments', [InteractionController4::class, 'comment'])->name('comments.store');
    Route::delete('/comments/{comment}', [InteractionController4::class, 'destroyComment'])->name('comments.destroy');

    // Follow
    Route::post('/users/{user}/follow', [InteractionController4::class, 'toggleFollow'])->name('users.follow');
});
// TV 5 (LINH) - QUẢN TRỊ (ADMIN)