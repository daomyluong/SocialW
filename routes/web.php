<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\PostController3;
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
    // Đường dẫn để hiện form đăng bài
    Route::get('/posts/create', [PostController3::class, 'create'])->name('posts3.create');
    
    // Đường dẫn để xử lý lưu dữ liệu
    Route::post('/posts/store', [PostController3::class, 'store'])->name('posts3.store');
    // Đường dẫn xem bài viết của riêng tôi
Route::get('/my-posts', [PostController3::class, 'myPosts'])->name('posts3.myPosts');
    // Route xóa bài viết
Route::delete('/posts/{id}', [PostController3::class, 'destroy'])->name('posts3.destroy');
    // Route Sửa - 1: Mở trang sửa (Cần file edit3.blade.php sau này)
Route::get('/posts/{id}/edit', [PostController3::class, 'edit'])->name('posts3.edit');
    // Route Sửa - 2: Lưu dữ liệu (Không cần file giao diện)
Route::put('/posts/{id}', [PostController3::class, 'update'])->name('posts3.update');

Route::get('/', [PostController3::class, 'index'])->name('home');
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