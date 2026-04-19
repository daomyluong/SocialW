<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ProfileController;
use App\Models\User;


Route::get('/', function () {
    return view('welcome');
});
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// TV 1 (ĐÀO) - HẠ TẦNG, TRANG CHỦ & TÌM KIẾM
Route::get('/', function () {
    return view('home'); 
})->name('home');

Route::get('/search', function () {
    return "Trang tìm kiếm";
})->name('search');

// TV 2 (LOAN) - NGƯỜI DÙNG (AUTH & PROFILE)
Route::get('/profile', function () {
    // Thêm dấu \App\Models\ trước chữ User
    $user = \App\Models\User::find(2); 

    return view('profile.show', compact('user'));
})->name('profile');
Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
// TV 3 (THANH) - BÀI VIẾT (POSTS)
// TV 4 (QUỲNH) - TƯƠNG TÁC (SOCIAL)
// TV 5 (LINH) - QUẢN TRỊ (ADMIN)
require __DIR__.'/auth.php';
