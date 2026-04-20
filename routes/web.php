<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController5;

Route::get('/welcome', function () {
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
Route::get('/post/{id}', [AdminController5::class, 'showPost'])->name('post.show');
// TV 4 (QUỲNH) - TƯƠNG TÁC (SOCIAL)


// TV 5 (LINH) - QUẢN TRỊ (ADMIN)

// Route tạm thời để test giao diện
Route::get('/admin_test', [AdminController5::class, 'testLayout']);
// Gom nhóm các trang admin lại cho chuyên nghiệp
Route::prefix('admin')->group(function () {
    
    // 1. TRANG DASHBOARD THẬT 
    Route::get('/dashboard', [AdminController5::class, 'dashboard'])->name('admin.dashboard');

    // 2. QUẢN LÝ NGƯỜI DÙNG 
    Route::get('/users', [AdminController5::class, 'manageUsers'])->name('admin.users.index');
    Route::post('/users', [AdminController5::class, 'storeUser'])->name('admin.users.store');
    Route::post('/users/{id}/update-role', [AdminController5::class, 'updateUserRole'])->name('admin.users.update_role');
    // Route xử lý Khóa/Mở khóa tài khoản (Dùng POST để bảo mật)
    Route::post('/users/{id}/toggle-status', [AdminController5::class, 'toggleUserStatus'])->name('admin.users.toggle_status');
    // Route xử lý Xóa mềm tài khoản
    Route::post('/users/{id}/delete', [AdminController5::class, 'deleteUser'])->name('admin.users.delete');
    
    // 3. QUẢN LÝ BÀI VIẾT 
    Route::get('/posts', [AdminController5::class, 'managePosts'])->name('admin.posts.index');
    Route::post('/posts/{id}/moderate', [AdminController5::class, 'moderatePost'])->name('admin.posts.moderate');
    Route::post('/posts/{id}/delete', [AdminController5::class, 'deletePost'])->name('admin.posts.delete');

    // 4. QUẢN LÝ BÌNH LUẬN (COMMENTS)
    Route::get('/comments', [AdminController5::class, 'manageComments'])->name('admin.comments.index');
    Route::post('/comments/{id}/delete', [AdminController5::class, 'deleteComment'])->name('admin.comments.delete');
    Route::post('/comments/quick-ban/{userId}', [AdminController5::class, 'quickBanUser'])->name('admin.comments.quick_ban');

    // 5. QUẢN LÝ BÁO CÁO (REPORTS)
    Route::get('/reports', [AdminController5::class, 'manageReports'])->name('admin.reports.index');
    // Route xử lý thao tác (Phán quyết) của Admin
    Route::post('/reports/process', [AdminController5::class, 'processReport'])->name('admin.reports.process');
});
