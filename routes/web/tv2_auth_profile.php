<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// TV2 (LOAN): auth and profile.
// Auth routes will be added by Loan (Breeze/Fortify/custom), avoid editing this file by others.
Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
