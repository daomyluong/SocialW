<?php

use App\Http\Controllers\FeedController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

// TV1 (DAO): home, search, infrastructure for feed/search page.
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/search/mentions', [SearchController::class, 'mentionSuggestions'])->name('search.mentions');
Route::get('/feed/latest', [FeedController::class, 'latest'])->name('feed.latest');
