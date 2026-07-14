<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MangaController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SitemapController;

Route::get('/', [MangaController::class, 'index'])->name('manga.index');
Route::get('/search', [MangaController::class, 'search'])->name('manga.search');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::view('/about', 'about')->name('about');

Route::post('/reviews', [ReviewController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('reviews.store');
