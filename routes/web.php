<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\PostController3;
use App\Http\Controllers\InteractionController4;
use App\Http\Controllers\BookmarkController3;

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


// Route để XEM chi tiết bài viết (Phương thức GET)
Route::get('/posts/{id}', [PostController3::class, 'show'])->name('posts3.show');

    // Route xóa bài viết
Route::delete('/posts/{id}', [PostController3::class, 'destroy'])->name('posts3.destroy');
    // Route Sửa - 1: Mở trang sửa (Cần file edit3.blade.php sau này)
Route::get('/posts/{id}/edit', [PostController3::class, 'edit'])->name('posts3.edit');
    // Route Sửa - 2: Lưu dữ liệu (Không cần file giao diện)
Route::put('/posts/{id}', [PostController3::class, 'update'])->name('posts3.update');

Route::get('/', [PostController3::class, 'index'])->name('home');
   // Route xem danh sách thông báo
Route::get('/notifications', [PostController3::class, 'notifications'])->name('notifications.index');
    // Route để xử lý đăng Story mới
Route::post('/stories3', [App\Http\Controllers\StoryController3::class, 'store'])->name('stories3.store');
// Route xử lý việc nhấn nút lưu 
Route::post('/bookmarks/toggle/{postId}', [BookmarkController3::class, 'toggleBookmark'])->name('bookmarks.toggle');

// Route để vào trang xem danh sách đã lưu
Route::get('/bookmarks', [BookmarkController3::class, 'index'])->name('bookmarks.index');
Route::get('/bookmarks/folders', [BookmarkController3::class, 'getFolders']);





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