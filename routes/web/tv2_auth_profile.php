<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// TV2 (LOAN): Auth and profile.

Route::get('/profile', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }
    return redirect()->route('profile.show', Auth::id());
})->name('profile');

// 2. Nhóm các route yêu cầu Đăng nhập (Auth)
Route::middleware('auth')->group(function (): void {
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    
    
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    
    Route::post('/profile/{id}/follow', [ProfileController::class, 'follow'])
        ->whereNumber('id')
        ->name('profile.follow');
});

// 3. Route công khai (Xem profile)
Route::get('/profile/{id}', [ProfileController::class, 'show'])
    ->whereNumber('id')
    ->name('profile.show');

Route::post('/logout', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])
    ->name('logout')
    ->middleware('auth');
Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('/login', [AuthenticatedSessionController::class, 'store']);

Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('/register', [RegisteredUserController::class, 'store']);