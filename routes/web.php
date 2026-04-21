<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;


Route::middleware('web')->group(function (): void {
    require __DIR__.'/web/tv1_home_search.php';
    require __DIR__.'/web/tv2_auth_profile.php';
    require __DIR__.'/web/tv3_posts.php';
    require __DIR__.'/web/tv4_social.php';
    require __DIR__.'/web/tv6_messages.php';
    require __DIR__.'/web/tv5_admin.php';
});


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::get('/welcome', function () {
    return view('welcome');
});

// TV 1 (ĐÀO) - HẠ TẦNG, TRANG CHỦ & TÌM KIẾM
Route::redirect('/home', '/');

// TV 2 (LOAN) - NGƯỜI DÙNG (AUTH & PROFILE)
Route::get('/profile', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    return redirect()->route('profile.show', Auth::id());
})->name('profile');
require __DIR__.'/auth.php';

