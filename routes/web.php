<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SearchController;

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
// TV 5 (LINH) - QUẢN TRỊ (ADMIN)