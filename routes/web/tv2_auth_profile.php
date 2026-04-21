<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// TV2 (LOAN): auth and profile.
Route::middleware('auth')->group(function (): void {
	Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
	Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
	Route::put('/profile/{id}/update', [ProfileController::class, 'update'])->whereNumber('id')->name('profile.update.user');
	Route::post('/profile/{id}/follow', [ProfileController::class, 'follow'])->name('profile.follow');
});

Route::get('/profile/{id}', [ProfileController::class, 'show'])
	->whereNumber('id')
	->name('profile.show');
