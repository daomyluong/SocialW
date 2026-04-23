<?php
use App\Http\Controllers\AdminController5;
use Illuminate\Support\Facades\Route;

// TV 5 (LINH) - QUẢN TRỊ (ADMIN)

// Route tạm thời để test giao diện (Không cần middleware nếu muốn test nhanh)
Route::get('/admin_test', [AdminController5::class, 'testLayout']);

Route::prefix('admin')->middleware(['auth', 'can:access-admin'])->group(function () {
   
    // 1. TRANG DASHBOARD
    Route::get('/dashboard', [AdminController5::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/back-home', [AdminController5::class, 'backHome'])->name('admin.back_home');

    // 2. QUẢN LÝ NGƯỜI DÙNG
    Route::get('/users', [AdminController5::class, 'manageUsers'])->name('admin.users.index');
    Route::post('/users', [AdminController5::class, 'storeUser'])->name('admin.users.store');
    Route::post('/users/{id}/update-role', [AdminController5::class, 'updateUserRole'])->name('admin.users.update_role');
    Route::post('/users/{id}/toggle-status', [AdminController5::class, 'toggleUserStatus'])->name('admin.users.toggle_status');
    Route::post('/users/{id}/delete', [AdminController5::class, 'deleteUser'])->name('admin.users.delete');
   
    // 3. QUẢN LÝ BÀI VIẾT
    Route::get('/posts', [AdminController5::class, 'managePosts'])->name('admin.posts.index');
    Route::post('/posts/{id}/toggle-visibility', [AdminController5::class, 'togglePostVisibility'])->name('admin.posts.toggle_visibility');
    Route::post('/posts/{id}/moderate', [AdminController5::class, 'moderatePost'])->name('admin.posts.moderate');
    Route::post('/posts/{id}/delete', [AdminController5::class, 'deletePost'])->name('admin.posts.delete');
    Route::get('/posts/{id}', [AdminController5::class, 'showPost'])->name('post.show');

    // 4. QUẢN LÝ BÌNH LUẬN
    Route::get('/comments', [AdminController5::class, 'manageComments'])->name('admin.comments.index');
    Route::post('/comments/{id}/toggle-visibility', [AdminController5::class, 'toggleCommentVisibility'])->name('admin.comments.toggle_visibility');
    Route::post('/comments/{id}/lock', [AdminController5::class, 'lockComment'])->name('admin.comments.lock');
    Route::post('/comments/{id}/delete', [AdminController5::class, 'deleteComment'])->name('admin.comments.delete');
    Route::post('/comments/quick-ban/{userId}', [AdminController5::class, 'quickBanUser'])->name('admin.comments.quick_ban');

    // 5. QUẢN LÝ BÁO CÁO
    Route::get('/reports', [AdminController5::class, 'manageReports'])->name('admin.reports.index');
    Route::post('/reports/process', [AdminController5::class, 'processReport'])->name('admin.reports.process');
});