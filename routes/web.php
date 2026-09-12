<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsArchiveController;
use App\Http\Controllers\NewsArticleController;
use App\Http\Controllers\NotFoundController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', HomeController::class)->name('home');

Route::get('/coaches', function () {
    return Inertia::render('Coaches');
})->name('coaches');

Route::get('/schedule', function () {
    return Inertia::render('Schedule');
})->name('schedule');

Route::get('/news', NewsArchiveController::class)->name('news');
Route::get('/news/{slug}', NewsArticleController::class)->name('news.show');

Route::get('/about', function () {
    return Inertia::render('About');
})->name('about');

Route::fallback(NotFoundController::class);
