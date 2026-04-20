<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\ProfileController;
use App\Models\User;


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// TV 1 (ĐÀO) - HẠ TẦNG, TRANG CHỦ & TÌM KIẾM
Route::get('/', function () {
    return view('home'); 
})->name('home');
Route::redirect('/home', '/');

Route::get('/search', function () {
    return "Trang tìm kiếm";
})->name('search');

// TV 2 (LOAN) - NGƯỜI DÙNG (AUTH & PROFILE)
Route::get('/profile', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }
    return redirect()->route('profile.show', auth()->id());
})->name('profile');

Route::middleware('auth')->group(function () {
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/{id}/follow', [ProfileController::class, 'follow'])->name('profile.follow');
});

Route::get('/profile/{id}', [ProfileController::class, 'show'])
    ->whereNumber('id')
    ->name('profile.show');
// TV 3 (THANH) - BÀI VIẾT (POSTS)
// TV 4 (QUỲNH) - TƯƠNG TÁC (SOCIAL)
// TV 5 (LINH) - QUẢN TRỊ (ADMIN)
require __DIR__.'/auth.php';
