<?php

use App\Http\Controllers\MessageController;
use Illuminate\Support\Facades\Route;

// TV6 (Messaging): direct and group chat.
Route::middleware('auth')->group(function (): void {
	Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
	Route::get('/messages/users/{user}', [MessageController::class, 'openPrivate'])->name('messages.private');
	Route::get('/messages/conversations/{conversation}', [MessageController::class, 'show'])->name('messages.show');
	Route::get('/messages/conversations/{conversation}/history', [MessageController::class, 'history'])->name('messages.history');
	Route::post('/messages/conversations/{conversation}/messages', [MessageController::class, 'store'])->name('messages.store');
});