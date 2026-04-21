<?php

use App\Http\Controllers\AdminController5;
use Illuminate\Support\Facades\Route;

// TV5 (LINH): admin moderation and management dashboard.
Route::prefix('admin')->name('admin.')->middleware(['auth', 'can:access-admin'])->group(function (): void {
    Route::get('/dashboard', [AdminController5::class, 'dashboard'])->name('dashboard');

    Route::get('/users', [AdminController5::class, 'manageUsers'])->name('users.index');
    Route::post('/users/{id}/toggle-status', [AdminController5::class, 'toggleUserStatus'])->name('users.toggle_status');
    Route::post('/users/{id}/delete', [AdminController5::class, 'deleteUser'])->name('users.delete');

    Route::get('/posts', [AdminController5::class, 'managePosts'])->name('posts.index');
    Route::post('/posts/{id}/delete', [AdminController5::class, 'deletePost'])->name('posts.delete');

    Route::get('/comments', [AdminController5::class, 'manageComments'])->name('comments.index');
    Route::post('/comments/{id}/delete', [AdminController5::class, 'deleteComment'])->name('comments.delete');
    Route::post('/comments/quick-ban/{userId}', [AdminController5::class, 'quickBanUser'])->name('comments.quick_ban');

    Route::get('/reports', [AdminController5::class, 'manageReports'])->name('reports.index');
    Route::post('/reports/process', [AdminController5::class, 'processReport'])->name('reports.process');
});
